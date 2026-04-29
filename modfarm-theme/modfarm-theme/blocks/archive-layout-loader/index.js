const { createElement: el } = wp.element;

wp.blocks.registerBlockType('modfarm/archive-layout-loader', {
    title: 'Archive Layout Loader',
    icon: 'screenoptions',
    category: 'modfarm',
    edit() {
        return el('p', {}, 'This block loads your Archive Header, Body, and Footer patterns dynamically.');
    },
    save() {
        return null; // Server-rendered
    }
});