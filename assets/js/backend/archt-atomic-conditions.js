( function () {
	'use strict';

	const CONTROL_TYPE = 'archt-display-conditions';
	const CONDITIONS = 'archt-display-conditions';
	const LIST = 'archt-condition-list';
	const ITEM = 'archt-condition';
	const DOMAIN = 'architect-complete-theme-builder-for-elementor';
	const NO_VALUE_OPERATORS = [ 'empty', 'not_empty' ];

	function register() {
		const v2 = window.elementorV2;

		if ( ! v2 || ! v2.editorEditingPanel || ! v2.editorControls || ! v2.ui || ! window.React ) {
			return false;
		}

		const registry = v2.editorEditingPanel.controlsRegistry;

		if ( ! registry || registry.get( CONTROL_TYPE ) ) {
			return true;
		}

		const h = window.React.createElement;
		const ui = v2.ui;
		const icons = v2.icons || {};
		const editorUi = v2.editorUi || {};
		const controls = v2.editorControls;
		const { createControl, useBoundProp, PropProvider, PropKeyProvider } = controls;
		const __ = ( text ) => ( window.wp && window.wp.i18n ? window.wp.i18n.__( text, DOMAIN ) : text );

		const operators = [
			{ value: '==', label: __( 'Equal' ) },
			{ value: '!=', label: __( 'Not Equal' ) },
			{ value: '<', label: __( 'Less Than' ) },
			{ value: '>', label: __( 'Greater Than' ) },
			{ value: 'empty', label: __( 'Empty' ) },
			{ value: 'not_empty', label: __( 'Not Empty' ) },
			{ value: 'contain', label: __( 'Contains' ) },
			{ value: 'not_contain', label: __( 'Doesn\'t Contain' ) },
		];

		const inner = ( prop, type ) => ( prop && prop.$$type === type ? prop.value : null );
		const plain = ( prop ) => ( prop && 'object' === typeof prop ? prop.value : undefined );
		const ownType = ( propType, type ) => ( propType && 'union' === propType.kind && propType.prop_types ? propType.prop_types[ type ] : propType );

		// Exposes the children of an object or array prop, so nested PropKeyProviders can bind to them.
		const Unwrap = ( { type, children } ) => {
			const bound = useBoundProp();

			return h( PropProvider, {
				propType: ownType( bound.propType, type ),
				value: inner( bound.value, type ),
				isDisabled: bound.isDisabled,
				setValue: ( value, options, meta ) => bound.setValue( null === value ? null : { $$type: type, value }, options, meta ),
			}, children );
		};

		const field = ( bind, label, control ) => h(
			PropKeyProvider,
			{ bind },
			h( ui.Stack, { gap: 0.5 }, label ? h( controls.ControlFormLabel, null, label ) : null, control )
		);

		const ConditionItem = ( { onRemove } ) => {
			const item = inner( useBoundProp().value, ITEM ) || {};
			const operator = plain( item.operator ) || 'not_empty';
			const isShortcode = 'shortcode' === plain( item.source );

			return h(
				Unwrap,
				{ type: ITEM },
				h(
					ui.Stack,
					{ gap: 1, sx: { p: 1, border: 1, borderColor: 'divider', borderRadius: 1 } },
					h(
						ui.Stack,
						{ direction: 'row', gap: 0.5, alignItems: 'center' },
						h( ui.Box, { sx: { flex: 1 } }, field( 'source', null, h( controls.SelectControl, {
							options: [ { value: 'field', label: __( 'Custom Field' ) }, { value: 'shortcode', label: __( 'Shortcode' ) } ],
							ariaLabel: __( 'Source' ),
						} ) ) ),
						h(
							ui.IconButton,
							{ size: 'tiny', onClick: onRemove, 'aria-label': __( 'Remove condition' ) },
							icons.TrashIcon ? h( icons.TrashIcon, { fontSize: 'tiny' } ) : '×'
						)
					),
					field( 'key', null, h( controls.TextControl, { placeholder: isShortcode ? '[shortcode attribute="value"]' : __( 'Field key or dynamic tag' ) } ) ),
					field( 'operator', null, h( controls.SelectControl, { options: operators, ariaLabel: __( 'Operator' ) } ) ),
					NO_VALUE_OPERATORS.includes( operator ) ? null : field( 'value', null, h( controls.TextControl, { placeholder: __( 'Value' ) } ) )
				)
			);
		};

		const ConditionList = () => {
			const bound = useBoundProp();
			const items = inner( bound.value, LIST ) || [];
			const save = ( next ) => bound.setValue( next.length ? { $$type: LIST, value: next } : null );

			return h(
				ui.Stack,
				{ gap: 1.5 },
				h(
					Unwrap,
					{ type: LIST },
					items.map( ( item, index ) => h(
						PropKeyProvider,
						{ key: index, bind: String( index ) },
						h( ConditionItem, { onRemove: () => save( items.filter( ( other, i ) => i !== index ) ) } )
					) )
				),
				h(
					ui.Button,
					{
						size: 'small',
						variant: 'outlined',
						color: 'secondary',
						startIcon: icons.PlusIcon ? h( icons.PlusIcon, { fontSize: 'tiny' } ) : null,
						onClick: () => save( items.concat( [ { $$type: ITEM, value: {} } ] ) ),
					},
					__( 'Add Condition' )
				)
			);
		};

		const DisplayConditionsControl = createControl( () => {
			const bound = useBoundProp();
			const data = inner( bound.value, CONDITIONS ) || {};
			const list = inner( data.conditions, LIST ) || [];
			const active = list.filter( ( item ) => {
				const key = ( inner( item, ITEM ) || {} ).key;
				return key && ( 'dynamic' === key.$$type || key.value );
			} ).length;
			const popupState = ui.usePopupState( { variant: 'popover' } );
			const title = __( 'Display Conditions' );

			return h(
				ui.Stack,
				{ direction: 'row', gap: 1, sx: { justifyContent: 'flex-end', alignItems: 'center' } },
				active ? h( ui.Typography, { variant: 'caption', color: 'text.secondary' }, String( active ) ) : null,
				h(
					ui.Tooltip,
					{ title, placement: 'top' },
					h(
						ui.IconButton,
						Object.assign( {
							size: 'tiny',
							disabled: bound.disabled,
							'aria-label': title,
							sx: { border: '1px solid', borderColor: active ? 'primary.main' : 'divider', borderRadius: 1 },
						}, ui.bindTrigger( popupState ) ),
						icons.SitemapIcon ? h( icons.SitemapIcon, { fontSize: 'tiny', color: active ? 'primary' : 'inherit' } ) : '≡'
					)
				),
				h(
					ui.Popover,
					Object.assign( {}, ui.bindPopover( popupState ), {
						disablePortal: true,
						slotProps: { paper: { sx: { borderRadius: 2, width: 280 } } },
						anchorOrigin: { vertical: 'bottom', horizontal: 'right' },
						transformOrigin: { vertical: 'top', horizontal: 'right' },
					} ),
					editorUi.PopoverHeader ? h( editorUi.PopoverHeader, { title, onClose: popupState.close } ) : null,
					h(
						Unwrap,
						{ type: CONDITIONS },
						h(
							ui.Stack,
							{ gap: 1.5, sx: { px: 1.5, pb: 1.5, maxHeight: 480, overflowY: 'auto' } },
							field( 'display', __( 'Display When' ), h( controls.SelectControl, {
								options: [ { value: 'all', label: __( 'All Conditions Are Met' ) }, { value: 'any', label: __( 'Any Condition is Met' ) } ],
								ariaLabel: __( 'Display When' ),
							} ) ),
							h( PropKeyProvider, { bind: 'conditions' }, h( ConditionList ) ),
							h( ui.Divider, null ),
							h(
								PropKeyProvider,
								{ bind: 'fallback_enable' },
								h(
									ui.Stack,
									{ direction: 'row', alignItems: 'center', justifyContent: 'space-between' },
									h( controls.ControlFormLabel, null, __( 'Fallback Content' ) ),
									h( controls.SwitchControl )
								)
							),
							plain( data.fallback_enable ) ? field( 'fallback', null, h( controls.TextAreaControl, { placeholder: __( 'Shown in place of this element when its conditions are not met.' ) } ) ) : null
						)
					)
				)
			);
		} );

		registry.register( CONTROL_TYPE, DisplayConditionsControl, 'two-columns' );

		return true;
	}

	if ( ! register() ) {
		window.addEventListener( 'elementor/init', register );

		if ( window.jQuery ) {
			window.jQuery( window ).on( 'elementor:init', register );
		}
	}
}() );
