(function () {
"use strict";

//iconData PASSED FROM LOCALIZE SCRIPT
const ICONS = createSVGWPElements(iconData);

function createSVGWPElements(iconData) {
    const result = [];

    for (const [key, value] of Object.entries(iconData)) {
        const label = wp.i18n.__(key, 'enable-button-icons');
        const svgAttributes = extractSvgAttributes(value);

        // LOOP THROUGH PATHS
        const pathElements = svgAttributes.pathD.map(pathDValue => (
            wp.element.createElement("path", {
                key: pathDValue, 
                d: pathDValue
            })
        ));

        const icon = wp.element.createElement("svg", {
            width: svgAttributes.width,
            height: svgAttributes.height,
            viewBox: svgAttributes.viewBox,
            xmlns: "http://www.w3.org/2000/svg"
        }, ...pathElements);

        result.push({ label, value: key, icon });
    }

    return result;
}


//ADD ATTRIBUTES TO THE BLOCK
function addAttributes(settings) {
	if ('core/button' !== settings.name) {
		return settings;
	}

	// Add the block visibility attributes.
	const iconAttributes = {
		icon: {
			type: 'string'
		},
		iconPositionLeft: {
			type: 'boolean',
			default: false
		}
	};
	const newSettings = {
		...settings,
		attributes: {
			...settings.attributes,
			...iconAttributes
		}
	};
	return newSettings;
}
wp.hooks.addFilter('blocks.registerBlockType', 'enable-button-icons/add-attributes', addAttributes);


//CREATE THE CORE BLOCK ADDITIONAL OPTIONS
function addInspectorControls(BlockEdit) {
	return function (props) {
		if (props.name !== 'core/button') {
			return BlockEdit(props);
		}

		var attributes = props.attributes,
			setAttributes = props.setAttributes;
		var currentIcon = attributes.icon,
			currentIconSVG = attributes.iconSVG,
			iconPositionLeft = attributes.iconPositionLeft;

		return wp.element.createElement(
			wp.element.Fragment,
			null,
			wp.element.createElement(BlockEdit, props),
			wp.element.createElement(wp.blockEditor.InspectorControls, null,
				wp.element.createElement(wp.components.PanelBody, {
					title: wp.i18n.__('Icon settings', 'enable-button-icons'),
					className: "button-icon-picker",
					initialOpen: true
				},
					wp.element.createElement(wp.components.PanelRow, null,
						wp.element.createElement(wp.components.__experimentalGrid, {
							className: "button-icon-picker__grid",
							columns: "5",
							gap: "0"
						},
							ICONS.map(function (icon, index) {
								var _icon$icon;
								return wp.element.createElement(wp.components.Button, {
									key: index,
									label: icon ? icon.label : '',
									isPressed: currentIcon === icon.value,
									className: "button-icon-picker__button",
									onClick: function () {
										setAttributes({
											icon: currentIcon === icon.value ? null : icon.value,
											iconSVG: currentIconSVG === icon.icon ? null : icon.icon
										});
									}
								}, (_icon$icon = icon.icon) !== null && _icon$icon !== void 0 ? _icon$icon : icon.value);
							})
						)
					),
					wp.element.createElement(wp.components.PanelRow, null,
						wp.element.createElement(wp.components.ToggleControl, {
							label: wp.i18n.__('Show icon on left', 'enable-button-icons'),
							checked: iconPositionLeft,
							onChange: function () {
								setAttributes({
									iconPositionLeft: !iconPositionLeft
								});
							}
						})
					)
				)
			)
		);
	};
}

wp.hooks.addFilter('editor.BlockEdit', 'enable-button-icons/add-inspector-controls', addInspectorControls);


//ADD CLASSES WITHIN THE EDITOR TO THE ELEMENT
function addClasses(BlockListBlock) {
	return function (props) {
		var name = props.name,
			attributes = props.attributes;

		if ('core/button' !== name || !attributes || !attributes.icon) {
			return wp.element.createElement(BlockListBlock, props);
		}		

		var classes = (props && props.className || '') + ' ' +
			(attributes.icon ? 'has-icon__' + attributes.icon : '') +
			(attributes.iconPositionLeft ? ' has-icon-position__left' : '');
		

		return wp.element.createElement(BlockListBlock, Object.assign({}, props, {
			className: classes
		}));
	};
}
wp.hooks.addFilter('editor.BlockListBlock', 'enable-button-icons/add-classes', addClasses);


//EXTRACT SVG ATTRIBUTES 
function extractSvgAttributes(svgMarkup) {
    const widthMatch = svgMarkup.match(/width="([^"]+)"/);
    const heightMatch = svgMarkup.match(/height="([^"]+)"/);
    const viewBoxMatch = svgMarkup.match(/viewBox="([^"]+)"/);

    const width = widthMatch ? widthMatch[1] : '22';
    const height = heightMatch ? heightMatch[1] : '20';
    const viewBox = viewBoxMatch ? viewBoxMatch[1] : '0 0 448 512';

    // Extract the 'd' attribute values for each <path>
    const pathMatches = svgMarkup.match(/<path d="([^"]+)"/g) || [];
    const pathDValues = pathMatches.map(match => match.match(/<path d="([^"]+)"/)[1]);

    return { width, height, viewBox, pathD: pathDValues };
}



})();
