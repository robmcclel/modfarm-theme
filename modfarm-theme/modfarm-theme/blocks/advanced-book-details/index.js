/* global window */
(function (wp) {
  const el = wp.element.createElement;
  const { useState, useEffect, Fragment } = wp.element;
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, InspectorControls } = wp.blockEditor || wp.editor;
  const {
    PanelBody, Button, SelectControl, ToggleControl, TextControl,
    __experimentalVStack: VStack, __experimentalHStack: HStack
  } = wp.components;
  const ServerSideRender = wp.serverSideRender;
  const { __ } = wp.i18n;

  // Fallback known BMS keys (keep short & useful; you can expand later)
  const KNOWN_BMS_KEYS = [
    "isbn13","isbn10","hard_isbn10","hard_isbn13","asin","asin_kindle","asin_paperback","asin_audiobook",
    "page_count","audiobook_narrator","publisher","translator","editor","publication_date","hardcover_publication_date",
    "audiobook_publication_date","audiobook_duration","language","series_position","price_ebook","price_ebook_list","price_paper", "price_paper_list","price_audio","price_audio_list","price_hard","price_hard_list"
  ];

  function useDiscovery() {
    const [taxes, setTaxes] = useState([]);
    const [meta, setMeta] = useState([]);
    const [loading, setLoading] = useState(false);

    const discover = () => {
      setLoading(true);
      // Hit our REST route (registered in render.php)
      wp.apiFetch({ path: "/modfarm/v1/advanced-book-details/discover" })
        .then((res) => {
          setTaxes(Array.isArray(res?.taxonomies) ? res.taxonomies : []);
          const keys = Array.isArray(res?.metaKeys) ? res.metaKeys : [];
          // Merge with known list (unique, keep order)
          const merged = Array.from(new Set([ ...KNOWN_BMS_KEYS, ...keys ]));
          setMeta(merged);
        })
        .catch(() => {
          // On error, just expose the fallback
          setTaxes([]);
          setMeta(KNOWN_BMS_KEYS);
        })
        .finally(() => setLoading(false));
    };

    useEffect(() => { discover(); }, []);
    return { taxes, meta, loading, discover };
  }

  function rowLabel(optionVal) {
    if (!optionVal) return "— Select —";
    if (optionVal.startsWith("tax:")) return optionVal.replace(/^tax:/, "") + " (Taxonomy)";
    if (optionVal.startsWith("meta:")) return optionVal.replace(/^meta:/, "") + " (Meta)";
    return optionVal;
  }

  function RowEditor({ value, index, onChange, onRemove, onMove, options, disabled }) {
    return el(VStack, { spacing: 2, className: "mfb-simple-row-editor" },
      el(HStack, { spacing: 6, alignment: "center", className: "mfb-simple-row-controls" },
        el("strong", null, `#${index + 1}`),
        el(Button, { isSmall: true, onClick: () => onMove(index, -1), icon: "arrow-up", disabled }),
        el(Button, { isSmall: true, onClick: () => onMove(index, +1), icon: "arrow-down", disabled }),
        el(Button, { isDestructive: true, isSmall: true, onClick: () => onRemove(index) }, __("Remove", "modfarm"))
      ),
      el(SelectControl, {
        label: __("Field", "modfarm"),
        value: value || "",
        options,
        onChange: (v) => onChange(index, v),
        disabled
      })
    );
  }

  registerBlockType("modfarm/advanced-book-details", {
    apiVersion: 2,
    title: __("Book Details (Advanced)", "modfarm"),
    icon: "list-view",
    category: "modfarm-theme",
    attributes: {
      rows: { type: "array", default: [] },
      hideEmpty: { type: "boolean", default: true },
      title: { type: "string", default: "" }
    },
    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();
      const { rows = [] } = attributes;
      const { taxes, meta, loading, discover } = useDiscovery();

      const options = [
        { label: "— Select —", value: "" },
        { label: "— Taxonomies —", value: "", disabled: true },
        ...taxes.map(t => ({ label: t.label || t.slug, value: "tax:" + t.slug })),
        { label: "— Meta (BMS & custom) —", value: "", disabled: true },
        ...meta.map(k => ({ label: k, value: "meta:" + k }))
      ];

      const addRow = () => setAttributes({ rows: [...rows, ""] });
      const changeRow = (idx, v) => {
        const next = rows.slice(); next[idx] = v; setAttributes({ rows: next });
      };
      const removeRow = (idx) => {
        const next = rows.slice(); next.splice(idx, 1); setAttributes({ rows: next });
      };
      const moveRow = (idx, dir) => {
        const j = idx + dir; if (j < 0 || j >= rows.length) return;
        const next = rows.slice(); const tmp = next[idx]; next[idx] = next[j]; next[j] = tmp;
        setAttributes({ rows: next });
      };

      const sidebar = el(InspectorControls, {},
        el(PanelBody, { title: __("Fields", "modfarm"), initialOpen: true },
          el(Button, { variant: "primary", onClick: addRow }, __("Add Field", "modfarm")),
          rows.length
            ? el(VStack, { spacing: 6, style: { marginTop: "10px" } },
                rows.map((val, i) =>
                  el(RowEditor, {
                    key: i, value: val, index: i, onChange: changeRow, onRemove: removeRow, onMove: moveRow,
                    options, disabled: loading
                  })
                )
              )
            : el("p", null, __("No fields yet. Click “Add Field”.", "modfarm")),
          el(HStack, { spacing: 8, style: { marginTop: "8px" } },
            el(Button, { onClick: discover, isBusy: loading }, __("Auto-discover fields", "modfarm"))
          )
        ),
        el(PanelBody, { title: __("Options", "modfarm"), initialOpen: false },
          el(ToggleControl, {
            label: __("Hide Empty Rows", "modfarm"),
            checked: !!attributes.hideEmpty,
            onChange: (v) => setAttributes({ hideEmpty: !!v })
          })
        ),
        el(PanelBody, { title: __("Heading (optional)", "modfarm"), initialOpen: false },
          el(TextControl, {
            label: __("Title", "modfarm"),
            value: attributes.title || "",
            onChange: (v) => setAttributes({ title: v })
          })
        )
      );

      return el(Fragment, {},
        el("div", blockProps, el(ServerSideRender, { block: "modfarm/advanced-book-details", attributes })),
        sidebar
      );
    },
  });
})(window.wp);
