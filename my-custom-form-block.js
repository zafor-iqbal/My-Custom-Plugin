(function(blocks, element) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;

    registerBlockType('my-custom-plugin/my-custom-form', {
        title: 'My Custom Form',
        icon: 'forms',
        category: 'widgets',

        edit: function() {
            return el(
                'p',
                {},
                'My Custom Form'
            );
        },

        save: function() {
            return null; // Use dynamic rendering in PHP
        }
    });
}(
    window.wp.blocks,
    window.wp.element
));
