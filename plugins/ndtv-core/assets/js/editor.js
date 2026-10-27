/**
 * Editor UI for the NDTV Core blocks.
 *
 * Written against the wp.* globals with no JSX so the plugin needs no build.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, ComboboxControl, TextControl, ToggleControl, SelectControl, Placeholder, Spinner } = wp.components;
	const { useSelect } = wp.data;
	const { __ } = wp.i18n;
	const ServerSideRender = wp.serverSideRender;

	/**
	 * Video picker: a searchable list over the ndtv_video CPT. In production the
	 * same control would query NDTV's player backend instead of WordPress.
	 */
	function VideoPicker( { value, onChange } ) {
		const videos = useSelect( ( select ) =>
			select( 'core' ).getEntityRecords( 'postType', 'ndtv_video', {
				per_page: 100,
				_fields: 'id,title,meta',
				orderby: 'date',
				order: 'desc',
			} )
		);

		if ( ! videos ) {
			return el( Spinner );
		}

		const options = videos.map( ( video ) => {
			const meta = video.meta || {};
			const tags = [ meta.kind === 'live' ? 'LIVE' : null, meta.orientation === 'vertical' ? 'Short' : null, meta.player_id ]
				.filter( Boolean )
				.join( ' · ' );
			return {
				value: video.id,
				label: ( video.title.rendered || __( '(untitled)', 'ndtv-core' ) ) + ( tags ? ' — ' + tags : '' ),
			};
		} );

		return el( ComboboxControl, {
			label: __( 'Video', 'ndtv-core' ),
			help: __( 'Search the video library by title or player ID.', 'ndtv-core' ),
			value: value || null,
			options,
			onChange: ( id ) => onChange( id ? parseInt( id, 10 ) : 0 ),
			__next40pxDefaultSize: true,
			__nextHasNoMarginBottom: true,
		} );
	}

	registerBlockType( 'ndtv/player', {
		edit( { attributes, setAttributes, context } ) {
			const blockProps = useBlockProps();
			const inVideoLoop = ! attributes.videoId && context.postType === 'ndtv_video';
			const hasSource = attributes.videoId || attributes.playerId;

			const inspector = el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: __( 'Video', 'ndtv-core' ) },
					el( VideoPicker, { value: attributes.videoId, onChange: ( videoId ) => setAttributes( { videoId } ) } ),
					el( TextControl, {
						label: __( 'Player ID (optional)', 'ndtv-core' ),
						help: __( 'Use a raw ID from the NDTV player backend instead of a library video.', 'ndtv-core' ),
						value: attributes.playerId,
						onChange: ( playerId ) => setAttributes( { playerId } ),
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
					} ),
					el( ToggleControl, {
						label: __( 'Show title', 'ndtv-core' ),
						checked: attributes.showTitle,
						onChange: ( showTitle ) => setAttributes( { showTitle } ),
						__nextHasNoMarginBottom: true,
					} ),
					el( ToggleControl, {
						label: __( 'Autoplay (muted)', 'ndtv-core' ),
						checked: attributes.autoplay,
						onChange: ( autoplay ) => setAttributes( { autoplay } ),
						__nextHasNoMarginBottom: true,
					} )
				)
			);

			let body;
			if ( inVideoLoop ) {
				body = el( 'div', { className: 'ndtv-player__frame ndtv-player__frame--stub' }, __( 'NDTV Player: plays the current video', 'ndtv-core' ) );
			} else if ( ! hasSource ) {
				body = el(
					Placeholder,
					{ icon: 'video-alt3', label: __( 'NDTV Player', 'ndtv-core' ), instructions: __( 'Pick a video from the library, or paste a player ID.', 'ndtv-core' ) },
					el( 'div', { style: { width: '100%', maxWidth: 420 } }, el( VideoPicker, { value: attributes.videoId, onChange: ( videoId ) => setAttributes( { videoId } ) } ) )
				);
			} else {
				body = el( ServerSideRender, { block: 'ndtv/player', attributes } );
			}

			return el( Fragment, null, inspector, el( 'div', blockProps, body ) );
		},
		save: () => null,
	} );

	registerBlockType( 'ndtv/badge', {
		edit( { attributes, setAttributes } ) {
			const labels = { duration: '4:12', live: 'LIVE', play: 'Play', section: 'Section', channel: 'NDTV 24x7' };
			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Badge', 'ndtv-core' ) },
						el( SelectControl, {
							label: __( 'Show', 'ndtv-core' ),
							value: attributes.type,
							options: [
								{ value: 'duration', label: __( 'Duration (or LIVE)', 'ndtv-core' ) },
								{ value: 'live', label: __( 'LIVE marker only', 'ndtv-core' ) },
								{ value: 'play', label: __( 'Play icon', 'ndtv-core' ) },
								{ value: 'section', label: __( 'Section name', 'ndtv-core' ) },
								{ value: 'channel', label: __( 'Channel', 'ndtv-core' ) },
							],
							onChange: ( type ) => setAttributes( { type } ),
							__next40pxDefaultSize: true,
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el( 'span', useBlockProps( { className: 'ndtv-badge is-' + attributes.type } ), labels[ attributes.type ] )
			);
		},
		save: () => null,
	} );

	/**
	 * Query Loop variations for the video rails. The `ndtvRail` key inside
	 * `query` is read server-side by inc/rails.php. Editor previews show the
	 * newest videos; the rail filter applies on the front end.
	 */
	const rails = [
		{ rail: 'latest', title: __( 'NDTV: Latest videos', 'ndtv-core' ), perPage: 8, ratio: '16/9', columns: 4 },
		{ rail: 'shorts', title: __( 'NDTV: Shorts rail', 'ndtv-core' ), perPage: 10, ratio: '9/16', columns: 5 },
		{ rail: 'live', title: __( 'NDTV: Live channels', 'ndtv-core' ), perPage: 6, ratio: '1', columns: 6 },
		{ rail: 'top', title: __( 'NDTV: Top videos (most viewed)', 'ndtv-core' ), perPage: 4, ratio: '16/9', columns: 4 },
		{ rail: 'related', title: __( 'NDTV: Related videos', 'ndtv-core' ), perPage: 3, ratio: '16/9', columns: 3 },
	];

	rails.forEach( ( { rail, title, perPage, ratio, columns } ) => {
		wp.blocks.registerBlockVariation( 'core/query', {
			name: 'ndtv-rail-' + rail,
			title,
			description: __( 'A video rail over the Videos library.', 'ndtv-core' ),
			icon: 'video-alt3',
			category: 'theme',
			scope: [ 'inserter' ],
			attributes: {
				className: 'ndtv-rail-' + rail,
				query: {
					perPage,
					pages: 0,
					offset: 0,
					postType: 'ndtv_video',
					order: 'desc',
					orderBy: 'date',
					author: '',
					search: '',
					exclude: [],
					sticky: '',
					inherit: false,
					ndtvRail: rail,
				},
			},
			isActive: ( attributes ) => attributes.query && attributes.query.ndtvRail === rail,
			innerBlocks: [
				[
					'core/post-template',
					{ layout: { type: 'grid', columnCount: columns } },
					[
						[
							'core/group',
							{ className: 'ndtv-thumb', layout: { type: 'default' } },
							[
								[ 'core/post-featured-image', { isLink: true, aspectRatio: ratio } ],
								[ 'ndtv/badge', { type: 'duration' } ],
							],
						],
						[ 'core/post-title', { level: 3, isLink: true } ],
					],
				],
			],
		} );
	} );

	registerBlockType( 'ndtv/market-strip', {
		edit( { attributes } ) {
			return el( 'div', useBlockProps(), el( ServerSideRender, { block: 'ndtv/market-strip', attributes } ) );
		},
		save: () => null,
	} );

	/**
	 * "NDTV source" panel on videos: the metadata the sync wrote, read-only.
	 */
	const SOURCE_FIELDS = [
		[ 'player_id', __( 'Player ID', 'ndtv-core' ) ],
		[ 'kind', __( 'Kind', 'ndtv-core' ) ],
		[ 'content_type', __( 'Content type', 'ndtv-core' ) ],
		[ 'orientation', __( 'Orientation', 'ndtv-core' ) ],
		[ 'duration', __( 'Duration (s)', 'ndtv-core' ) ],
		[ 'channel', __( 'Channel', 'ndtv-core' ) ],
		[ 'video_from', __( 'Origin', 'ndtv-core' ) ],
		[ 'stream_url', __( 'HLS stream', 'ndtv-core' ) ],
		[ 'mp4_url', __( 'MP4', 'ndtv-core' ) ],
		[ 'preview_url', __( 'Hover preview', 'ndtv-core' ) ],
		[ 'related_ids', __( 'Related (player IDs)', 'ndtv-core' ) ],
		[ 'synced_at', __( 'Last synced', 'ndtv-core' ) ],
	];

	function SourcePanel() {
		const { postType, meta } = useSelect( ( select ) => {
			const editor = select( 'core/editor' );
			return {
				postType: editor.getCurrentPostType(),
				meta: editor.getEditedPostAttribute( 'meta' ) || {},
			};
		} );

		if ( 'ndtv_video' !== postType || ! meta.player_id ) {
			return null;
		}

		const rows = SOURCE_FIELDS.filter( ( [ key ] ) => meta[ key ] ).map( ( [ key, label ] ) => {
			const value = String( meta[ key ] );
			const isUrl = /^https?:\/\//.test( value );
			return el(
				'div',
				{ key, style: { marginBottom: '8px' } },
				el( 'div', { style: { fontSize: '11px', textTransform: 'uppercase', color: '#757575' } }, label ),
				isUrl
					? el( 'a', { href: value, target: '_blank', rel: 'noopener', style: { wordBreak: 'break-all' } }, value )
					: el( 'code', { style: { wordBreak: 'break-all' } }, value )
			);
		} );

		return el(
			wp.editor.PluginDocumentSettingPanel,
			{ name: 'ndtv-source', title: __( 'NDTV source', 'ndtv-core' ), className: 'ndtv-source-panel' },
			el(
				'p',
				null,
				__( 'Synced from NDTV\'s video feed. Streams and posters stay on NDTV\'s CDNs; the next sync overwrites these fields.', 'ndtv-core' )
			),
			meta.source_url ? el( 'p', null, el( 'a', { href: meta.source_url, target: '_blank', rel: 'noopener' }, __( 'View on ndtv.com', 'ndtv-core' ) ) ) : null,
			rows
		);
	}

	if ( wp.plugins && wp.editor && wp.editor.PluginDocumentSettingPanel ) {
		wp.plugins.registerPlugin( 'ndtv-source', { render: SourcePanel } );
	}
} )( window.wp );
