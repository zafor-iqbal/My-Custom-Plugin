const { registerBlockType } = wp.blocks;

registerBlockType('my-custom-plugin/my-custom-report', {
    title: 'My Custom Report',
    icon: 'analytics',
    category: 'common',
    edit: function(props) {
        return 'My Custom Report'; // Basic placeholder for the editor.
    },
    save: function() {
        return null; // Dynamic block, rendered with PHP.
    }
});
