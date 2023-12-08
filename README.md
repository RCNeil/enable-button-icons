# enable-button-icons
A plugin to modify the Wordpress `core/button` block to include icons based on Nick Diego's (@ndiego) original plugin example - https://github.com/ndiego/enable-button-icons

Notable changes:

* There is a directory named `icons` within the plugin directory. Upload your SVG's there.
* The SVGs must have `width`, `height`, and `viewBox` attributes. They must have `path` elements only, though you can have as many `path` elements as needed. (no `polygon`, `rect`, etc.)
* The PHP will generate the markup on both the backend and frontend to implement them
* I changed the React to vanilla JS because I'm an old fart

Modify the CSS for your needs.
