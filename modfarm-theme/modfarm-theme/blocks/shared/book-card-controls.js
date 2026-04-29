/* global wp */
const { __ } = wp.i18n;
const { useSelect } = wp.data;
const {
    InspectorControls,
    ColorPalette,
} = wp.blockEditor || wp.editor;
const {
    PanelBody,
    PanelRow,
    ToggleControl,
    SelectControl,
} = wp.components;

/**
 * Reusable controls for ModFarm book-card design.
 *
 * Props:
 *   - attributes
 *   - setAttributes
 *   - prefix (string) optional, in case you want per-block namespacing later
 */
export default function BookCardControls( { attributes, setAttributes, prefix = 'card' } ) {
    const withPrefix = ( key ) => `${ prefix }${ key.charAt(0).toUpperCase() }${ key.slice(1) }`;

    // Attribute names (kept centralized so all blocks use the same schema)
    const attr = {
        useGlobal:            withPrefix( 'UseGlobal' ),
        coverShape:           withPrefix( 'CoverShape' ),
        buttonShape:          withPrefix( 'ButtonShape' ),
        sampleShape:          withPrefix( 'SampleShape' ),
        ctaMode:              withPrefix( 'CtaMode' ),
        shadowStyle:          withPrefix( 'ShadowStyle' ),
        showTitle:            withPrefix( 'ShowTitle' ),
        showSeries:           withPrefix( 'ShowSeries' ),
        showPrimary:          withPrefix( 'ShowPrimary' ),
        showSample:           withPrefix( 'ShowSample' ),
        buttonBg:             withPrefix( 'ButtonBg' ),
        buttonText:           withPrefix( 'ButtonText' ),
        sampleBg:             withPrefix( 'SampleBg' ),
        sampleText:           withPrefix( 'SampleText' ),
    };

    const values = {
        useGlobal:   attributes[ attr.useGlobal ] ?? true,
        coverShape:  attributes[ attr.coverShape ] || 'inherit',
        buttonShape: attributes[ attr.buttonShape ] || 'inherit',
        sampleShape: attributes[ attr.sampleShape ] || 'inherit',
        ctaMode:     attributes[ attr.ctaMode ]     || 'inherit',
        shadowStyle: attributes[ attr.shadowStyle ] || 'inherit',
        showTitle:   attributes[ attr.showTitle ]   || 'inherit',
        showSeries:  attributes[ attr.showSeries ]  || 'inherit',
        showPrimary: attributes[ attr.showPrimary ] || 'inherit',
        showSample:  attributes[ attr.showSample ]  || 'inherit',
        buttonBg:    attributes[ attr.buttonBg ]    || '',
        buttonText:  attributes[ attr.buttonText ]  || '',
        sampleBg:    attributes[ attr.sampleBg ]    || '',
        sampleText:  attributes[ attr.sampleText ]  || '',
    };

    // Get theme palette so the picker looks like the screenshot
    const themeColors = useSelect(
        ( select ) => {
            const settings = select( 'core/block-editor' ).getSettings();
            return settings?.colors || [];
        },
        []
    );

    const set = ( key, value ) => {
        setAttributes( { [ attr[ key ] ]: value } );
    };

    const disableOverrides = !!values.useGlobal;

    return (
        <InspectorControls>
            <PanelBody
                title={ __( 'Book Card Defaults', 'modfarm' ) }
                initialOpen={ true }
            >
                <ToggleControl
                    label={ __( 'Use ModFarm global defaults', 'modfarm' ) }
                    checked={ values.useGlobal }
                    onChange={ ( val ) => set( 'useGlobal', val ) }
                    help={
                        values.useGlobal
                            ? __( 'Card shape, spacing, visibility, and colors follow ModFarm Settings.', 'modfarm' )
                            : __( 'Customize this block independently of the global defaults.', 'modfarm' )
                    }
                />
            </PanelBody>

            <PanelBody
                title={ __( 'Card Layout & Effects', 'modfarm' ) }
                initialOpen={ true }
            >
                <PanelRow>
                    <SelectControl
                        label={ __( 'Cover Shape', 'modfarm' ) }
                        value={ values.coverShape }
                        onChange={ ( v ) => set( 'coverShape', v ) }
                        disabled={ disableOverrides }
                        options={ [
                            { label: __( 'Inherit (global)', 'modfarm' ), value: 'inherit' },
                            { label: __( 'Square', 'modfarm' ), value: 'square' },
                            { label: __( 'Rounded', 'modfarm' ), value: 'rounded' },
                        ] }
                    />
                </PanelRow>
                <PanelRow>
                    <SelectControl
                        label={ __( 'Primary Button Shape', 'modfarm' ) }
                        value={ values.buttonShape }
                        onChange={ ( v ) => set( 'buttonShape', v ) }
                        disabled={ disableOverrides }
                        options={ [
                            { label: __( 'Inherit (global)', 'modfarm' ), value: 'inherit' },
                            { label: __( 'Square', 'modfarm' ), value: 'square' },
                            { label: __( 'Rounded', 'modfarm' ), value: 'rounded' },
                            { label: __( 'Pill', 'modfarm' ), value: 'pill' },
                        ] }
                    />
                </PanelRow>
                <PanelRow>
                    <SelectControl
                        label={ __( 'Sample Button Shape', 'modfarm' ) }
                        value={ values.sampleShape }
                        onChange={ ( v ) => set( 'sampleShape', v ) }
                        disabled={ disableOverrides }
                        options={ [
                            { label: __( 'Inherit (global)', 'modfarm' ), value: 'inherit' },
                            { label: __( 'Square', 'modfarm' ), value: 'square' },
                            { label: __( 'Rounded', 'modfarm' ), value: 'rounded' },
                            { label: __( 'Pill', 'modfarm' ), value: 'pill' },
                        ] }
                    />
                </PanelRow>
                <PanelRow>
                    <SelectControl
                        label={ __( 'CTA Spacing', 'modfarm' ) }
                        value={ values.ctaMode }
                        onChange={ ( v ) => set( 'ctaMode', v ) }
                        disabled={ disableOverrides }
                        options={ [
                            { label: __( 'Inherit (global)', 'modfarm' ), value: 'inherit' },
                            { label: __( 'Joined (touching cover)', 'modfarm' ), value: 'joined' },
                            { label: __( 'Gap (space between)', 'modfarm' ), value: 'gap' },
                        ] }
                    />
                </PanelRow>
                <PanelRow>
                    <SelectControl
                        label={ __( 'Shadow Style', 'modfarm' ) }
                        value={ values.shadowStyle }
                        onChange={ ( v ) => set( 'shadowStyle', v ) }
                        disabled={ disableOverrides }
                        options={ [
                            { label: __( 'Inherit (global)', 'modfarm' ), value: 'inherit' },
                            { label: __( 'Flat', 'modfarm' ), value: 'flat' },
                            { label: __( 'Small shadow', 'modfarm' ), value: 'shadow-sm' },
                            { label: __( 'Medium shadow', 'modfarm' ), value: 'shadow-md' },
                            { label: __( 'Large shadow', 'modfarm' ), value: 'shadow-lg' },
                            { label: __( 'Embossed', 'modfarm' ), value: 'emboss' },
                        ] }
                    />
                </PanelRow>
            </PanelBody>

            <PanelBody
                title={ __( 'Visibility', 'modfarm' ) }
                initialOpen={ false }
            >
                <PanelRow>
                    <SelectControl
                        label={ __( 'Title', 'modfarm' ) }
                        value={ values.showTitle }
                        onChange={ ( v ) => set( 'showTitle', v ) }
                        disabled={ disableOverrides }
                        options={ [
                            { label: __( 'Inherit (global)', 'modfarm' ), value: 'inherit' },
                            { label: __( 'Show', 'modfarm' ), value: 'show' },
                            { label: __( 'Hide', 'modfarm' ), value: 'hide' },
                        ] }
                    />
                </PanelRow>
                <PanelRow>
                    <SelectControl
                        label={ __( 'Series', 'modfarm' ) }
                        value={ values.showSeries }
                        onChange={ ( v ) => set( 'showSeries', v ) }
                        disabled={ disableOverrides }
                        options={ [
                            { label: __( 'Inherit (global)', 'modfarm' ), value: 'inherit' },
                            { label: __( 'Show', 'modfarm' ), value: 'show' },
                            { label: __( 'Hide', 'modfarm' ), value: 'hide' },
                        ] }
                    />
                </PanelRow>
                <PanelRow>
                    <SelectControl
                        label={ __( 'Primary Button', 'modfarm' ) }
                        value={ values.showPrimary }
                        onChange={ ( v ) => set( 'showPrimary', v ) }
                        disabled={ disableOverrides }
                        options={ [
                            { label: __( 'Inherit (global)', 'modfarm' ), value: 'inherit' },
                            { label: __( 'Show', 'modfarm' ), value: 'show' },
                            { label: __( 'Hide', 'modfarm' ), value: 'hide' },
                        ] }
                    />
                </PanelRow>
                <PanelRow>
                    <SelectControl
                        label={ __( 'Sample Button', 'modfarm' ) }
                        value={ values.showSample }
                        onChange={ ( v ) => set( 'showSample', v ) }
                        disabled={ disableOverrides }
                        options={ [
                            { label: __( 'Inherit (global)', 'modfarm' ), value: 'inherit' },
                            { label: __( 'Show', 'modfarm' ), value: 'show' },
                            { label: __( 'Hide', 'modfarm' ), value: 'hide' },
                        ] }
                    />
                </PanelRow>
            </PanelBody>

            <PanelBody
                title={ __( 'Local Colors (optional)', 'modfarm' ) }
                initialOpen={ false }
            >
                <p className="components-base-control__help">
                    { __( 'Leave empty to use ModFarm global button colors. These overrides affect only this block.', 'modfarm' ) }
                </p>

                <PanelRow>
                    <div>
                        <strong>{ __( 'Primary Button Background', 'modfarm' ) }</strong>
                        <ColorPalette
                            colors={ themeColors }
                            value={ values.buttonBg }
                            onChange={ ( color ) => set( 'buttonBg', color ) }
                            disableCustomColors={ false }
                            clearable={ true }
                            disabled={ disableOverrides }
                        />
                    </div>
                </PanelRow>

                <PanelRow>
                    <div>
                        <strong>{ __( 'Primary Button Text', 'modfarm' ) }</strong>
                        <ColorPalette
                            colors={ themeColors }
                            value={ values.buttonText }
                            onChange={ ( color ) => set( 'buttonText', color ) }
                            disableCustomColors={ false }
                            clearable={ true }
                            disabled={ disableOverrides }
                        />
                    </div>
                </PanelRow>

                <PanelRow>
                    <div>
                        <strong>{ __( 'Sample Button Background', 'modfarm' ) }</strong>
                        <ColorPalette
                            colors={ themeColors }
                            value={ values.sampleBg }
                            onChange={ ( color ) => set( 'sampleBg', color ) }
                            disableCustomColors={ false }
                            clearable={ true }
                            disabled={ disableOverrides }
                        />
                    </div>
                </PanelRow>

                <PanelRow>
                    <div>
                        <strong>{ __( 'Sample Button Text', 'modfarm' ) }</strong>
                        <ColorPalette
                            colors={ themeColors }
                            value={ values.sampleText }
                            onChange={ ( color ) => set( 'sampleText', color ) }
                            disableCustomColors={ false }
                            clearable={ true }
                            disabled={ disableOverrides }
                        />
                    </div>
                </PanelRow>
            </PanelBody>
        </InspectorControls>
    );
}