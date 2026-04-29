(function () {
  const { createElement: el, Fragment } = wp.element;
  const { InspectorControls, useBlockProps } = wp.blockEditor;
  const {
    PanelBody,
    SelectControl,
    TextControl,
    RangeControl,
    ColorPalette,
    BaseControl,
    Button
  } = wp.components;
  const ServerSideRender = wp.serverSideRender;

  wp.blocks.registerBlockType("modfarm/book-page-series", {
    title: "Book Page Series",
    icon: "book",
    category: "modfarm-book-page",
    attributes: {
      displayMode: { type: "string", default: "auto" },
      volumeLabel: { type: "string", default: "Book" },
      customLabel: { type: "string", default: "" },
      alignment: { type: "string", default: "left" },
      fontSize: { type: "number", default: 16 },

      // empty = inherit theme/site text color
      textColor: { type: "string", default: "" }
    },

    edit: (props) => {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const clearTextColor = () => setAttributes({ textColor: "" });

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: "Series Display Settings", initialOpen: true },

            el(SelectControl, {
              label: "Display Mode",
              value: attributes.displayMode,
              options: [
                { label: "Auto (Series + Position)", value: "auto" },
                { label: "Custom Label", value: "custom" },
                { label: "Do Not Display", value: "none" }
              ],
              onChange: (val) => setAttributes({ displayMode: val })
            }),

            attributes.displayMode === "auto" &&
              el(TextControl, {
                label: "Volume Label (e.g. Book, Vol, Part)",
                value: attributes.volumeLabel,
                onChange: (val) => setAttributes({ volumeLabel: val })
              }),

            attributes.displayMode === "custom" &&
              el(TextControl, {
                label: "Custom Label",
                value: attributes.customLabel,
                onChange: (val) => setAttributes({ customLabel: val })
              }),

            el(SelectControl, {
              label: "Alignment",
              value: attributes.alignment,
              options: [
                { label: "Left", value: "left" },
                { label: "Center", value: "center" },
                { label: "Right", value: "right" }
              ],
              onChange: (val) => setAttributes({ alignment: val })
            }),

            el(RangeControl, {
              label: "Font Size",
              value: attributes.fontSize,
              min: 8,
              max: 48,
              step: 1,
              onChange: (val) => setAttributes({ fontSize: val })
            }),

            el("p", { style: { fontSize: "13px", marginBottom: "6px", color: "#555" } },
              "Optional: Override text color. Leave empty to inherit."
            ),

            el(
              BaseControl,
              { label: "Text Color (override)" },
              el(ColorPalette, {
                value: attributes.textColor || "",
                onChange: (val) => setAttributes({ textColor: val || "" })
              })
            ),

            el(Button, {
              variant: "secondary",
              onClick: clearTextColor,
              style: { marginTop: "6px" }
            }, "Clear Color (Inherit)")
          )
        ),

        el(
          "div",
          blockProps,
          el(ServerSideRender, {
            block: "modfarm/book-page-series",
            attributes: attributes
          })
        )
      );
    },

    save: () => null
  });
})();