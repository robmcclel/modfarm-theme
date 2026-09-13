(function (wp) {
  const el = wp.element.createElement;
  const ServerSideRender = wp.serverSideRender;
  const Fragment = wp.element.Fragment;
  const { InspectorControls, PanelColorSettings } = wp.blockEditor;
  const {
    PanelBody,
    SelectControl,
    ToggleControl,
    RangeControl
  } = wp.components;
  const { useState, useEffect } = wp.element;

  const FONT_OPTIONS = [{ label: 'Inherit', value: 'inherit' }].concat(
    window.modfarmNavigationFonts && Array.isArray(window.modfarmNavigationFonts.fonts)
      ? window.modfarmNavigationFonts.fonts
      : []
  );

  // Register (or override) the block with the new attribute.
  wp.blocks.registerBlockType('modfarm/navigation-menu', {
    attributes: {
      mobileBg: { type: 'string', default: '' },
      mobileColor: { type: 'string', default: '' },
      mobilePresentation: {"type":"string","default":"overlay"},
      drawerSide: {"type":"string","default":"right"},
      showDescriptions: {"type":"boolean","default":false},
      coverWidth: {"type":"number","default":56},
      iconSize: {"type":"number","default":24},
      imageGap: {"type":"number","default":16},
      dropdownWidth: {"type":"number","default":320},
      layoutType:   { type: 'string',  default: 'simple' },
      leftMenu:     { type: 'number',  default: 0 },
      rightMenu:    { type: 'number',  default: 0 },
      centerContent:{ type: 'string',  default: 'site-title' },
      simpleAlign:  { type: 'string',  default: 'left' },
      mode:         { type: 'string',  default: 'header' },
      localStyle:   { type: 'boolean', default: false },
      navBg:        { type: 'string',  default: '' },
      navColor:     { type: 'string',  default: '' },
      navHover:     { type: 'string',  default: '' },
      submenuBg:    { type: 'string',  default: '' },
      submenuColor: { type: 'string',  default: '' },
      fontFamily:   { type: 'string',  default: 'inherit' },
      fontSize:     { type: 'number',  default: 16 },
      transparent:  { type: 'boolean', default: false },

      // NEW: keep this nav expanded on mobile (no overlay/toggle)
      noCollapse:   { type: 'boolean', default: false }
    },

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const {
        layoutType,
        leftMenu,
        rightMenu,
        centerContent,
        simpleAlign,
        mode,
        localStyle,
        navBg,
        navColor,
        navHover,
        submenuBg,
        submenuColor,
        fontFamily,
        fontSize,
        transparent,
        noCollapse
      } = attributes;

      const [menus, setMenus] = useState([]);

      useEffect(() => {
        wp.apiFetch({ path: '/wp/v2/menus' }).then(setMenus);
      }, []);

      const menuOptions = [{ value: 0, label: 'Select a menu' }].concat(
        menus.map((menu) => ({ value: menu.id, label: menu.name }))
      );

      const centerOptionsSimple = [
        { label: 'None', value: 'none' },
        { label: 'Site Title', value: 'site-title' },
        { label: 'Site Logo', value: 'site-logo' },
        { label: 'Site Icon', value: 'site-icon' },
        { label: 'Icon + Title', value: 'site-icon-title' }
      ];

      const centerOptionsSplit = [
        { label: 'Site Title', value: 'site-title' },
        { label: 'Site Logo', value: 'site-logo' },
        { label: 'Site Icon', value: 'site-icon' },
        { label: 'Icon + Title', value: 'site-icon-title' }
      ];

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: 'Navigation Settings', initialOpen: true },
            el(SelectControl, {
              label: 'Layout Type',
              value: layoutType,
              options: [
                { label: 'Simple Menu', value: 'simple' },
                { label: 'Split Menu', value: 'split' }
              ],
              onChange: (value) => setAttributes({ layoutType: value })
            }),
            el(SelectControl, {
              label: 'Usage Mode',
              value: mode,
              options: [
                { label: 'Header', value: 'header' },
                { label: 'Footer', value: 'footer' }
              ],
              onChange: (value) => setAttributes({ mode: value })
            }),

            // NEW: Do not collapse on mobile
            el(ToggleControl, {
              label: 'Do not collapse on mobile',
              checked: !!noCollapse,
              onChange: (val) => setAttributes({ noCollapse: !!val }),
              help:
                'When enabled, this menu instance stays visible on phones (no hamburger/overlay).'
            }),

            el(ToggleControl, {
              label: 'Enable Local Style Overrides',
              checked: localStyle,
              onChange: (value) => {
                setAttributes({ localStyle: value });
                if (!value) setAttributes({ transparent: false });
              }
            }),
            localStyle &&
              el(
                Fragment,
                {},
                el(ToggleControl, {
                  label: 'Transparent Background',
                  checked: transparent,
                  onChange: (value) => setAttributes({ transparent: value })
                }),
                el(PanelColorSettings, {
                  title: 'Color Overrides',
                  initialOpen: true,
                  colorSettings: [
                    { label: 'Mobile Menu Background', value: attributes.mobileBg, onChange: (c) => setAttributes({ mobileBg: c || '' }) },
                    { label: 'Mobile Menu Text', value: attributes.mobileColor, onChange: (c) => setAttributes({ mobileColor: c || '' }) },
                    { label: 'Nav Background', value: navBg, onChange: (c) => setAttributes({ navBg: c }) },
                    { label: 'Nav Text Color', value: navColor, onChange: (c) => setAttributes({ navColor: c }) },
                    { label: 'Nav Hover Color', value: navHover, onChange: (c) => setAttributes({ navHover: c }) },
                    { label: 'Submenu Background', value: submenuBg, onChange: (c) => setAttributes({ submenuBg: c }) },
                    { label: 'Submenu Text Color', value: submenuColor, onChange: (c) => setAttributes({ submenuColor: c }) }
                  ]
                }),
                el(SelectControl, {
                  label: 'Font Family',
                  value: fontFamily,
                  options: FONT_OPTIONS,
                  onChange: (value) => setAttributes({ fontFamily: value })
                }),
                el(RangeControl, {
                  label: 'Font Size (px)',
                  value: fontSize,
                  min: 10,
                  max: 48,
                  step: 1,
                  onChange: (value) => setAttributes({ fontSize: value })
                })
              )
          ),
          el(PanelBody, { title: 'Mobile Navigation', initialOpen: false },
            el(SelectControl, {
              label: 'Presentation', value: attributes.mobilePresentation || 'overlay', disabled: !!noCollapse,
              options: [{label:'Below header',value:'below'},{label:'Side drawer',value:'drawer'},{label:'Full-screen overlay',value:'overlay'}],
              onChange: value => setAttributes({mobilePresentation:value})
            }),
            attributes.mobilePresentation === 'drawer' && el(SelectControl, {
              label:'Drawer side', value:attributes.drawerSide || 'right',
              options:[{label:'Right',value:'right'},{label:'Left',value:'left'}],
              onChange:value => setAttributes({drawerSide:value}),
              help:'Full width on phones, half width on portrait tablets, and about a third on wider tablets (minimum 320px).'
            })
          ),
          el(PanelBody, { title:'Menu Images and Descriptions', initialOpen:false },
            el('p', {}, 'Choose images in Appearance → Menus or Customizer → Menus. Enable Image in advanced menu properties.'),
            el(ToggleControl, {label:'Show descriptions',checked:!!attributes.showDescriptions,onChange:value=>setAttributes({showDescriptions:value})}),
            ...[['coverWidth','Cover width',32,120],['iconSize','Icon size',16,64],['imageGap','Image / text gap',4,32],['dropdownWidth','Dropdown width',220,480]].map(([key,label,min,max])=>el(RangeControl, {
              key, label:label+' (px)', value:attributes[key], min,max, onChange:value=>setAttributes({[key]:value})
            }))
          ),
          el(
            PanelBody,
            { title: 'Menu Selection', initialOpen: false },
            layoutType === 'simple' &&
              el(
                Fragment,
                {},
                el(SelectControl, {
                  label: 'Menu',
                  value: leftMenu,
                  options: menuOptions,
                  onChange: (value) => setAttributes({ leftMenu: parseInt(value) })
                }),
                el(SelectControl, {
                  label: 'Alignment',
                  value: simpleAlign,
                  options: [
                    { label: 'Left', value: 'left' },
                    { label: 'Center', value: 'center' },
                    { label: 'Right', value: 'right' }
                  ],
                  onChange: (value) => setAttributes({ simpleAlign: value })
                }),
                el(SelectControl, {
                  label: 'Optional Center Content',
                  value: centerContent || 'site-title',
                  options: centerOptionsSimple,
                  onChange: (value) => setAttributes({ centerContent: value }),
                  help: 'Logo/Icon sizing comes from Appearance → ModFarm Settings → Navigation.'
                })
              ),
            layoutType === 'split' &&
              el(
                Fragment,
                {},
                el(SelectControl, {
                  label: 'Left Menu',
                  value: leftMenu,
                  options: menuOptions,
                  onChange: (value) => setAttributes({ leftMenu: parseInt(value) })
                }),
                el(SelectControl, {
                  label: 'Right Menu',
                  value: rightMenu,
                  options: menuOptions,
                  onChange: (value) => setAttributes({ rightMenu: parseInt(value) })
                }),
                mode === 'header' &&
                  el(SelectControl, {
                    label: 'Center Content',
                    value: centerContent || 'site-title',
                    options: centerOptionsSplit,
                    onChange: (value) => setAttributes({ centerContent: value }),
                    help: 'Logo/Icon sizing comes from Appearance → ModFarm Settings → Navigation.'
                  })
              )
          )
        ),
        // Editor preview
        el(ServerSideRender, {
          block: 'modfarm/navigation-menu',
          attributes: attributes
        })
      );
    }
  });
})(window.wp);
