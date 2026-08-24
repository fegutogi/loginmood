( function () {
	'use strict';

	const form = document.getElementById( 'loginmood-settings-form' );
	if ( ! form ) {
		return;
	}

	const preview = document.getElementById( 'loginmood-preview' );
	const previewForm = preview.querySelector( '.loginmood-preview-form' );
	const previewLogo = document.getElementById( 'loginmood-preview-logo' );
	const previewFooter = document.getElementById( 'loginmood-preview-footer' );
	const previewButton = preview.querySelector( '.loginmood-preview-row button' );
	const previewLink = preview.querySelector( '.loginmood-preview-link' );
	const backgroundType = document.getElementById( 'loginmood-background-type' );
	const contrastStatus = document.getElementById( 'loginmood-contrast-status' );
	const paletteInput = document.getElementById( 'loginmood-brand-palette' );
	const paletteFile = document.getElementById( 'loginmood-palette-file' );
	const paletteDropzone = document.getElementById( 'loginmood-palette-dropzone' );
	const paletteSwatches = document.getElementById( 'loginmood-palette-swatches' );
	const paletteTable = document.getElementById( 'loginmood-palette-table' );
	const paletteTableSummary = document.getElementById( 'loginmood-palette-table-summary' );
	const paletteTableBody = document.getElementById( 'loginmood-palette-table-body' );
	const clearPalette = document.getElementById( 'loginmood-clear-palette' );
	const logoThumb = document.getElementById( 'loginmood-logo-thumb' );
	const logoOptions = form.querySelector( '.loginmood-logo-options' );
	const logoBackgroundOption = form.querySelector( '.loginmood-logo-background-option' );
	const logoBorderColorOption = form.querySelector( '.loginmood-logo-border-color-option' );
	const logoShadowColorOption = form.querySelector( '.loginmood-logo-shadow-color-option' );
	const logoGapOption = form.querySelector( '.loginmood-logo-gap-option' );
	let brandPalette = [];
	try {
		brandPalette = normalizePalette( JSON.parse( paletteInput.value ) );
	} catch ( error ) {
		brandPalette = [];
	}
	const presets = {
		light: { background_color: '#f1f5f9', panel_color: '#ffffff', primary_color: '#2563eb', text_color: '#1e293b', background_text_color: '#1e293b', link_color: '#1d4ed8', button_text_color: '#ffffff', field_background_color: '#ffffff', field_text_color: '#1e293b', border_radius: 12, control_radius: 8 },
		dark: { background_color: '#0f172a', panel_color: '#1e293b', primary_color: '#38bdf8', text_color: '#f8fafc', background_text_color: '#f8fafc', link_color: '#7dd3fc', button_text_color: '#082f49', field_background_color: '#0f172a', field_text_color: '#f8fafc', border_radius: 12, control_radius: 8 },
		ocean: { background_color: '#ecfeff', panel_color: '#ffffff', primary_color: '#0f766e', text_color: '#134e4a', background_text_color: '#134e4a', link_color: '#0f766e', button_text_color: '#ffffff', field_background_color: '#f0fdfa', field_text_color: '#134e4a', border_radius: 18, control_radius: 25 },
	};

	function field( key ) {
		return form.querySelector( '[name="fegutogi_loginmood_settings[' + key + ']"]' );
	}

	function isHexColor( value ) {
		return /^#[0-9a-f]{6}$/i.test( value );
	}

	function colorValue( key ) {
		const input = field( key );
		const value = input.value.trim();
		return isHexColor( value ) ? value : input.dataset.lastColor;
	}

	function normalizeHex( color ) {
		let value = color.replace( '#', '' ).toUpperCase();
		if ( value.length === 3 ) {
			value = value.split( '' ).map( function ( character ) {
				return character + character;
			} ).join( '' );
		}
		return '#' + value;
	}

	function rgbToHex( red, green, blue ) {
		return '#' + [ red, green, blue ].map( function ( channel ) {
			return Math.max( 0, Math.min( 255, channel ) ).toString( 16 ).padStart( 2, '0' );
		} ).join( '' ).toUpperCase();
	}

	function cleanColorName( name ) {
		return String( name || '' )
			.replace( /^\s*(?:--)?/, '' )
			.replace( /\s*[:=,;\-–—]+\s*$/, '' )
			.replace( /\s+/g, ' ' )
			.trim()
			.slice( 0, 80 );
	}

	function normalizePalette( entries ) {
		const palette = [];
		const add = function ( color, name ) {
			if ( typeof color !== 'string' ) {
				return;
			}
			const normalized = normalizeHex( color );
			if ( ! isHexColor( normalized ) ) {
				return;
			}
			const existing = palette.find( function ( entry ) { return entry.color === normalized; } );
			const normalizedName = cleanColorName( name );
			if ( existing ) {
				if ( ! existing.name && normalizedName ) {
					existing.name = normalizedName;
				}
				return;
			}
			if ( palette.length < 32 ) {
				palette.push( { name: normalizedName, color: normalized } );
			}
		};

		if ( Array.isArray( entries ) ) {
			entries.forEach( function ( entry ) {
				if ( typeof entry === 'string' ) {
					add( entry, '' );
				} else if ( entry && typeof entry === 'object' ) {
					add( entry.color || entry.hex || entry.value, entry.name || entry.label || entry.title );
				}
			} );
		}
		return palette;
	}

	function hexToRgba( hex, alpha ) {
		const clean = hex.replace( '#', '' );
		return 'rgba(' + parseInt( clean.slice( 0, 2 ), 16 ) + ', ' + parseInt( clean.slice( 2, 4 ), 16 ) + ', ' + parseInt( clean.slice( 4, 6 ), 16 ) + ', ' + alpha + ')';
	}

	function extractPaletteColors( contents ) {
		const colors = [];
		const addColor = function ( color, name ) {
			const normalized = normalizeHex( color );
			if ( ! isHexColor( normalized ) ) {
				return;
			}
			const existing = colors.find( function ( entry ) { return entry.color === normalized; } );
			const normalizedName = cleanColorName( name );
			if ( existing ) {
				if ( ! existing.name && normalizedName ) {
					existing.name = normalizedName;
				}
			} else if ( colors.length < 32 ) {
				colors.push( { name: normalizedName, color: normalized } );
			}
		};

		try {
			const json = JSON.parse( contents );
			if ( Array.isArray( json ) ) {
				normalizePalette( json ).forEach( function ( entry ) { addColor( entry.color, entry.name ); } );
			} else if ( json && typeof json === 'object' ) {
				const source = Array.isArray( json.colors ) ? json.colors : json;
				if ( Array.isArray( source ) ) {
					normalizePalette( source ).forEach( function ( entry ) { addColor( entry.color, entry.name ); } );
				} else {
					Object.keys( source ).forEach( function ( name ) { addColor( source[name], name ); } );
				}
			}
		} catch ( error ) {
			// Continue with text-based formats.
		}

		contents.split( /\r?\n/ ).forEach( function ( line ) {
			const gplColor = line.match( /^\s*(\d{1,3})\s+(\d{1,3})\s+(\d{1,3})(?:\s+(.*?))?\s*$/ );
			if ( gplColor ) {
				addColor( rgbToHex( parseInt( gplColor[1], 10 ), parseInt( gplColor[2], 10 ), parseInt( gplColor[3], 10 ) ), gplColor[4] );
				return;
			}
			const matches = line.match( /#(?:[0-9a-f]{6}|[0-9a-f]{3})(?![0-9a-f])/gi ) || [];
			matches.forEach( function ( color ) {
				let name = line.replace( color, '' ).replace( /[{};]/g, '' );
				name = name.replace( /^\s*(?:color\s*)?[:=]?\s*/i, '' );
				addColor( color, name );
			} );
		} );

		if ( colors.length === 0 ) {
			( contents.match( /\b[0-9a-f]{6}\b/gi ) || [] ).forEach( function ( color ) { addColor( color, '' ); } );
		}

		return colors;
	}

	function parseCsvLine( line ) {
		const cells = [];
		let cell = '';
		let quoted = false;

		for ( let index = 0; index < line.length; index++ ) {
			const character = line[index];
			if ( character === '"' ) {
				if ( quoted && line[index + 1] === '"' ) {
					cell += '"';
					index++;
				} else {
					quoted = ! quoted;
				}
			} else if ( character === ',' && ! quoted ) {
				cells.push( cell.trim() );
				cell = '';
			} else {
				cell += character;
			}
		}

		cells.push( cell.trim() );
		return cells;
	}

	function extractCsvColors( contents ) {
		const rows = contents.split( /\r?\n/ ).map( parseCsvLine ).filter( function ( row ) {
			return row.some( function ( cell ) { return cell !== ''; } );
		} );
		const colors = [];
		if ( rows.length === 0 ) {
			return colors;
		}

		const headings = rows[0].map( function ( heading ) { return heading.toLowerCase(); } );
		const redIndex = headings.findIndex( function ( heading ) { return heading === 'r' || heading === 'red' || heading === 'rojo'; } );
		const greenIndex = headings.findIndex( function ( heading ) { return heading === 'g' || heading === 'green' || heading === 'verde'; } );
		const blueIndex = headings.findIndex( function ( heading ) { return heading === 'b' || heading === 'blue' || heading === 'azul'; } );
		const hexIndex = headings.findIndex( function ( heading ) { return heading === 'hex' || heading === 'color' || heading === 'colour'; } );
		const nameIndex = headings.findIndex( function ( heading ) { return heading === 'name' || heading === 'nombre' || heading === 'label' || heading === 'title'; } );
		const hasRgbHeadings = redIndex >= 0 && greenIndex >= 0 && blueIndex >= 0;
		const hasHeadings = hasRgbHeadings || hexIndex >= 0 || nameIndex >= 0;
		const addColor = function ( color, name ) {
			const normalized = normalizeHex( color );
			if ( ! isHexColor( normalized ) ) {
				return;
			}
			const existing = colors.find( function ( entry ) { return entry.color === normalized; } );
			if ( ! existing && colors.length < 32 ) {
				colors.push( { name: cleanColorName( name ), color: normalized } );
			} else if ( existing && ! existing.name && name ) {
				existing.name = cleanColorName( name );
			}
		};

		rows.slice( hasHeadings ? 1 : 0 ).forEach( function ( row ) {
			let channels;
			let color = '';
			const name = nameIndex >= 0 ? row[nameIndex] : '';
			if ( hasRgbHeadings ) {
				channels = [ row[redIndex], row[greenIndex], row[blueIndex] ];
			} else {
				const numeric = row.filter( function ( cell ) { return /^\d{1,3}$/.test( cell ); } );
				channels = numeric.length === 3 ? numeric : [];
			}

			if ( channels.length === 3 && channels.every( function ( channel ) { return /^\d{1,3}$/.test( channel ) && Number( channel ) <= 255; } ) ) {
				color = rgbToHex( Number( channels[0] ), Number( channels[1] ), Number( channels[2] ) );
			} else if ( hexIndex >= 0 ) {
				color = row[hexIndex];
			} else {
				color = row.find( function ( cell ) { return /^#?[0-9a-f]{6}$/i.test( cell ); } ) || '';
			}
			const inferredName = name || row.find( function ( cell ) { return cell !== color && ! /^\d{1,3}$/.test( cell ) && ! /^#?[0-9a-f]{6}$/i.test( cell ); } ) || '';
			addColor( color, inferredName );
		} );

		return colors.slice( 0, 32 );
	}

	function aseLabToRgb( lightness, a, b ) {
		const l = lightness <= 1 ? lightness * 100 : lightness;
		const fy = ( l + 16 ) / 116;
		const fx = fy + ( a / 500 );
		const fz = fy - ( b / 200 );
		const pivot = function ( value ) {
			const cube = value * value * value;
			return cube > 0.008856 ? cube : ( value - ( 16 / 116 ) ) / 7.787;
		};
		const x50 = 0.96422 * pivot( fx );
		const y50 = pivot( fy );
		const z50 = 0.82521 * pivot( fz );
		const x = ( 0.9555766 * x50 ) + ( -0.0230393 * y50 ) + ( 0.0631636 * z50 );
		const y = ( -0.0282895 * x50 ) + ( 1.0099416 * y50 ) + ( 0.0210077 * z50 );
		const z = ( 0.0122982 * x50 ) + ( -0.020483 * y50 ) + ( 1.3299098 * z50 );
		const encode = function ( value ) {
			const linear = Math.max( 0, Math.min( 1, value ) );
			return 255 * ( linear <= 0.0031308 ? 12.92 * linear : ( 1.055 * Math.pow( linear, 1 / 2.4 ) ) - 0.055 );
		};

		return rgbToHex(
			Math.round( encode( ( 3.2404542 * x ) + ( -1.5371385 * y ) + ( -0.4985314 * z ) ) ),
			Math.round( encode( ( -0.969266 * x ) + ( 1.8760108 * y ) + ( 0.041556 * z ) ) ),
			Math.round( encode( ( 0.0556434 * x ) + ( -0.2040259 * y ) + ( 1.0572252 * z ) ) )
		);
	}

	function extractAseColors( buffer ) {
		const view = new DataView( buffer );
		const colors = [];
		if ( view.byteLength < 12 || String.fromCharCode( view.getUint8( 0 ), view.getUint8( 1 ), view.getUint8( 2 ), view.getUint8( 3 ) ) !== 'ASEF' ) {
			return colors;
		}

		const blockCount = view.getUint32( 8, false );
		let offset = 12;
		for ( let block = 0; block < blockCount && offset + 6 <= view.byteLength && colors.length < 32; block++ ) {
			const type = view.getUint16( offset, false );
			const length = view.getUint32( offset + 2, false );
			const end = offset + 6 + length;
			offset += 6;
			if ( end > view.byteLength ) {
				break;
			}

			if ( type === 0x0001 && offset + 2 <= end ) {
				const nameLength = view.getUint16( offset, false );
				let name = '';
				for ( let character = 0; character < Math.max( 0, nameLength - 1 ); character++ ) {
					name += String.fromCharCode( view.getUint16( offset + 2 + ( character * 2 ), false ) );
				}
				const modelOffset = offset + 2 + ( nameLength * 2 );
				if ( modelOffset + 4 <= end ) {
					const model = String.fromCharCode( view.getUint8( modelOffset ), view.getUint8( modelOffset + 1 ), view.getUint8( modelOffset + 2 ), view.getUint8( modelOffset + 3 ) );
					const valuesOffset = modelOffset + 4;
					let color = '';
					if ( model === 'RGB ' && valuesOffset + 12 <= end ) {
						color = rgbToHex( Math.round( view.getFloat32( valuesOffset, false ) * 255 ), Math.round( view.getFloat32( valuesOffset + 4, false ) * 255 ), Math.round( view.getFloat32( valuesOffset + 8, false ) * 255 ) );
					} else if ( model === 'CMYK' && valuesOffset + 16 <= end ) {
						const c = view.getFloat32( valuesOffset, false );
						const m = view.getFloat32( valuesOffset + 4, false );
						const y = view.getFloat32( valuesOffset + 8, false );
						const k = view.getFloat32( valuesOffset + 12, false );
						color = rgbToHex( Math.round( 255 * ( 1 - Math.min( 1, c + k ) ) ), Math.round( 255 * ( 1 - Math.min( 1, m + k ) ) ), Math.round( 255 * ( 1 - Math.min( 1, y + k ) ) ) );
					} else if ( model === 'Gray' && valuesOffset + 4 <= end ) {
						const gray = Math.round( view.getFloat32( valuesOffset, false ) * 255 );
						color = rgbToHex( gray, gray, gray );
					} else if ( model === 'LAB ' && valuesOffset + 12 <= end ) {
						color = aseLabToRgb( view.getFloat32( valuesOffset, false ), view.getFloat32( valuesOffset + 4, false ), view.getFloat32( valuesOffset + 8, false ) );
					}

					if ( isHexColor( color ) && ! colors.some( function ( entry ) { return entry.color === color; } ) ) {
						colors.push( { name: cleanColorName( name ), color: color } );
					}
				}
			}

			offset = end;
		}

		return colors;
	}

	function syncPaletteSelect( key, color ) {
		const select = form.querySelector( '.loginmood-palette-select[data-color-key="' + key + '"]' );
		if ( ! select ) {
			return;
		}

		let option = Array.from( select.options ).find( function ( candidate ) {
			return candidate.value.toUpperCase() === color.toUpperCase();
		} );
		select.querySelectorAll( '[data-custom-color]' ).forEach( function ( customOption ) {
			if ( customOption !== option ) {
				customOption.remove();
			}
		} );

		if ( ! option ) {
			option = new Option( window.FegutogiLoginMood.paletteCustom + ' · ' + color.toUpperCase(), color.toUpperCase(), true, true );
			option.dataset.customColor = 'true';
			select.appendChild( option );
		}

		select.value = option.value;
		const picker = form.querySelector( '.loginmood-color-picker[data-color-key="' + key + '"]' );
		if ( picker ) {
			picker.value = color;
		}
		updatePaletteDropdown( select );
	}

	function paletteLabel( entry ) {
		return ( entry.name ? entry.name + ' · ' : '' ) + entry.color.toUpperCase();
	}

	function updatePaletteDropdown( select ) {
		const dropdown = select.nextElementSibling;
		if ( ! dropdown || ! dropdown.classList.contains( 'loginmood-palette-dropdown' ) ) {
			return;
		}
		const button = dropdown.querySelector( '.loginmood-palette-toggle' );
		const selected = brandPalette.find( function ( entry ) { return entry.color === select.value.toUpperCase(); } );
		const color = selected ? selected.color : select.value.toUpperCase();
		button.querySelector( '.loginmood-palette-dot' ).style.background = color || 'transparent';
		button.querySelector( '.loginmood-palette-label' ).textContent = selected ? paletteLabel( selected ) : ( color ? window.FegutogiLoginMood.paletteCustom + ' · ' + color : window.FegutogiLoginMood.palettePlaceholder );
	}

	function renderPaletteDropdown( select ) {
		let dropdown = select.nextElementSibling;
		if ( dropdown && dropdown.classList.contains( 'loginmood-palette-dropdown' ) ) {
			dropdown.remove();
		}

		dropdown = document.createElement( 'div' );
		dropdown.className = 'loginmood-palette-dropdown';
		const toggle = document.createElement( 'button' );
		toggle.type = 'button';
		toggle.className = 'button loginmood-palette-toggle';
		toggle.setAttribute( 'aria-haspopup', 'listbox' );
		toggle.setAttribute( 'aria-expanded', 'false' );
		toggle.innerHTML = '<span class="loginmood-palette-dot" aria-hidden="true"></span><span class="loginmood-palette-label"></span><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>';
		const menu = document.createElement( 'div' );
		menu.className = 'loginmood-palette-menu';
		menu.hidden = true;
		menu.setAttribute( 'role', 'listbox' );

		brandPalette.forEach( function ( entry ) {
			const option = document.createElement( 'button' );
			option.type = 'button';
			option.className = 'loginmood-palette-option';
			option.setAttribute( 'role', 'option' );
			option.dataset.color = entry.color;
			option.innerHTML = '<span class="loginmood-palette-dot" aria-hidden="true"></span><span></span>';
			option.querySelector( '.loginmood-palette-dot' ).style.background = entry.color;
			option.querySelector( 'span:last-child' ).textContent = paletteLabel( entry );
			option.addEventListener( 'click', function () {
				setColor( select.dataset.colorKey, entry.color );
				menu.hidden = true;
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			} );
			menu.appendChild( option );
		} );

		toggle.addEventListener( 'click', function () {
			const opening = menu.hidden;
			document.querySelectorAll( '.loginmood-palette-menu' ).forEach( function ( otherMenu ) { otherMenu.hidden = true; } );
			document.querySelectorAll( '.loginmood-palette-toggle' ).forEach( function ( otherToggle ) { otherToggle.setAttribute( 'aria-expanded', 'false' ); } );
			menu.hidden = ! opening;
			toggle.setAttribute( 'aria-expanded', opening ? 'true' : 'false' );
		} );

		dropdown.appendChild( toggle );
		dropdown.appendChild( menu );
		select.classList.add( 'screen-reader-text' );
		select.insertAdjacentElement( 'afterend', dropdown );
		updatePaletteDropdown( select );
	}

	function setColor( key, color ) {
		const normalized = normalizeHex( color );
		if ( ! isHexColor( normalized ) ) {
			return;
		}
		const input = field( key );
		input.value = normalized;
		input.dataset.lastColor = normalized;
		syncPaletteSelect( key, normalized );
		updatePreview();
	}

	function renderPaletteControls() {
		paletteInput.value = JSON.stringify( brandPalette );
		paletteSwatches.innerHTML = '';
		paletteTableBody.innerHTML = '';
		paletteTable.hidden = brandPalette.length === 0;
		paletteTableSummary.textContent = window.FegutogiLoginMood.paletteTableLabel + ( brandPalette.length ? ' · ' + brandPalette.length : '' );

		if ( brandPalette.length === 0 ) {
			const empty = document.createElement( 'span' );
			empty.className = 'loginmood-palette-empty';
			empty.textContent = window.FegutogiLoginMood.paletteEmpty;
			paletteSwatches.appendChild( empty );
		} else {
			const label = document.createElement( 'span' );
			label.className = 'loginmood-palette-count';
			label.textContent = window.FegutogiLoginMood.paletteLoaded + ' · ' + brandPalette.length;
			paletteSwatches.appendChild( label );
			brandPalette.forEach( function ( entry ) {
				const swatch = document.createElement( 'span' );
				swatch.className = 'loginmood-palette-swatch';
				swatch.style.background = entry.color;
				swatch.title = paletteLabel( entry );
				paletteSwatches.appendChild( swatch );
			} );

			brandPalette.slice().sort( function ( first, second ) {
				const firstName = first.name || '';
				const secondName = second.name || '';
				return firstName.localeCompare( secondName, undefined, { sensitivity: 'base' } ) || first.color.localeCompare( second.color );
			} ).forEach( function ( entry ) {
				const row = document.createElement( 'tr' );
				const colorCell = document.createElement( 'td' );
				const dot = document.createElement( 'span' );
				dot.className = 'loginmood-palette-dot';
				dot.style.background = entry.color;
				dot.title = paletteLabel( entry );
				colorCell.appendChild( dot );

				const nameCell = document.createElement( 'td' );
				nameCell.textContent = entry.name || window.FegutogiLoginMood.paletteUnnamed;
				if ( ! entry.name ) {
					nameCell.className = 'loginmood-muted';
				}

				const hexCell = document.createElement( 'td' );
				const code = document.createElement( 'code' );
				code.textContent = entry.color;
				hexCell.appendChild( code );
				row.append( colorCell, nameCell, hexCell );
				paletteTableBody.appendChild( row );
			} );
		}

		form.querySelectorAll( '.loginmood-palette-select' ).forEach( function ( select ) {
			const key = select.dataset.colorKey;
			const current = colorValue( key ).toUpperCase();
			select.innerHTML = '';
			select.appendChild( new Option( window.FegutogiLoginMood.palettePlaceholder, '' ) );
			brandPalette.forEach( function ( entry ) {
				const option = new Option( '● ' + paletteLabel( entry ), entry.color );
				select.appendChild( option );
			} );
			syncPaletteSelect( key, current );
			renderPaletteDropdown( select );
		} );
	}

	function updateBackgroundPanels() {
		document.querySelectorAll( '[data-background-panel]' ).forEach( function ( panel ) {
			panel.hidden = panel.dataset.backgroundPanel !== backgroundType.value;
		} );
	}

	function updatePreview() {
		const type = backgroundType.value;
		if ( type === 'gradient' ) {
			preview.style.background = 'linear-gradient(' + field( 'gradient_angle' ).value + 'deg, ' + colorValue( 'gradient_start' ) + ', ' + colorValue( 'gradient_end' ) + ')';
		} else if ( type === 'image' && preview.dataset.backgroundImage ) {
			preview.style.background = 'url("' + preview.dataset.backgroundImage.replace( /["\\]/g, '\\$&' ) + '") center / cover no-repeat';
		} else {
			preview.style.background = colorValue( 'background_color' );
		}

		previewForm.style.background = colorValue( 'panel_color' );
		previewForm.style.color = colorValue( 'text_color' );
		previewForm.style.borderRadius = field( 'border_radius' ).value + 'px';
		previewButton.style.background = colorValue( 'primary_color' );
		previewButton.style.color = colorValue( 'button_text_color' );
		previewButton.style.borderRadius = field( 'control_radius' ).value + 'px';
		const controlPadding = Math.min( 26, 12 + Math.round( parseInt( field( 'control_radius' ).value, 10 ) * 0.28 ) );
		previewForm.querySelectorAll( 'input[type="text"], input[type="password"]' ).forEach( function ( input ) {
			input.style.background = colorValue( 'field_background_color' );
			input.style.color = colorValue( 'field_text_color' );
			input.style.borderRadius = field( 'control_radius' ).value + 'px';
			input.style.paddingLeft = controlPadding + 'px';
			input.style.paddingRight = controlPadding + 'px';
		} );
		const previewPassword = previewForm.querySelector( '.loginmood-preview-password input' );
		const previewPasswordIcon = previewForm.querySelector( '.loginmood-preview-password .dashicons' );
		previewPassword.style.paddingRight = controlPadding + 38 + 'px';
		previewPasswordIcon.style.color = colorValue( 'field_text_color' );
		previewPasswordIcon.style.right = Math.max( 2, controlPadding - 10 ) + 'px';
		previewLink.style.color = colorValue( 'link_color' );
		previewFooter.style.color = colorValue( 'background_text_color' );
		previewFooter.textContent = field( 'footer_text' ).value;
		updateLogoPreview();

		updateContrastStatus();
	}

	function updateLogoPreview() {
		const logoHidden = field( 'hide_logo' ).checked;
		previewLogo.hidden = logoHidden;
		logoOptions.hidden = logoHidden;
		const width = parseInt( field( 'logo_width' ).value, 10 ) || 180;
		const shape = field( 'logo_shape' ).value;
		const borderStyle = field( 'logo_border_style' ).value;
		const shadowStyle = field( 'logo_shadow_style' ).value;
		const borderWidth = { none: 0, thin: 1, strong: 3 }[ borderStyle ];
		const shadow = {
			none: 'none',
			soft: '0 10px 28px ' + hexToRgba( colorValue( 'logo_shadow_color' ), 0.24 ),
			strong: '0 16px 42px ' + hexToRgba( colorValue( 'logo_shadow_color' ), 0.42 ),
		}[ shadowStyle ];
		const image = previewLogo.querySelector( 'img' );
		let height = 90;
		const clipped = shape !== 'none';
		const alphaOutline = {
			none: '',
			thin: 'drop-shadow(1px 0 0 ' + colorValue( 'logo_border_color' ) + ') drop-shadow(-1px 0 0 ' + colorValue( 'logo_border_color' ) + ') drop-shadow(0 1px 0 ' + colorValue( 'logo_border_color' ) + ') drop-shadow(0 -1px 0 ' + colorValue( 'logo_border_color' ) + ')',
			strong: 'drop-shadow(2px 0 0 ' + colorValue( 'logo_border_color' ) + ') drop-shadow(-2px 0 0 ' + colorValue( 'logo_border_color' ) + ') drop-shadow(0 2px 0 ' + colorValue( 'logo_border_color' ) + ') drop-shadow(0 -2px 0 ' + colorValue( 'logo_border_color' ) + ')',
		}[ borderStyle ];
		const alphaShadow = {
			none: '',
			soft: 'drop-shadow(0 10px 14px ' + hexToRgba( colorValue( 'logo_shadow_color' ), 0.32 ) + ')',
			strong: 'drop-shadow(0 16px 20px ' + hexToRgba( colorValue( 'logo_shadow_color' ), 0.52 ) + ')',
		}[ shadowStyle ];

		if ( shape === 'circle' ) {
			height = width;
		} else if ( image && image.naturalWidth && image.naturalHeight ) {
			height = Math.round( width * image.naturalHeight / image.naturalWidth );
		}

		previewLogo.style.width = width + 'px';
		previewLogo.style.height = height + 'px';
		previewLogo.style.marginBottom = field( 'logo_panel_gap' ).value + 'px';
		previewLogo.style.borderRadius = shape === 'circle' ? '50%' : ( shape === 'rounded' ? '18px' : '0' );
		previewLogo.style.background = clipped ? colorValue( 'logo_background_color' ) : 'transparent';
		previewLogo.style.border = clipped ? borderWidth + 'px solid ' + colorValue( 'logo_border_color' ) : '0';
		previewLogo.style.boxShadow = clipped ? shadow : 'none';
		previewLogo.style.filter = clipped ? 'none' : ( alphaOutline + ' ' + alphaShadow ).trim() || 'none';
		previewLogo.style.overflow = clipped ? 'hidden' : 'visible';
		logoBackgroundOption.hidden = ! clipped;
		logoBorderColorOption.hidden = borderStyle === 'none';
		logoShadowColorOption.hidden = shadowStyle === 'none';
		logoGapOption.hidden = logoHidden || ! parseInt( field( 'logo_id' ).value, 10 );

		if ( image ) {
			image.style.height = '100%';
			image.style.width = '100%';
			image.style.objectFit = shape === 'circle' ? 'cover' : 'contain';
		}
	}

	function channelToLinear( channel ) {
		const value = channel / 255;
		return value <= 0.04045 ? value / 12.92 : Math.pow( ( value + 0.055 ) / 1.055, 2.4 );
	}

	function luminance( hex ) {
		const clean = hex.replace( '#', '' );
		const red = parseInt( clean.slice( 0, 2 ), 16 );
		const green = parseInt( clean.slice( 2, 4 ), 16 );
		const blue = parseInt( clean.slice( 4, 6 ), 16 );
		return ( 0.2126 * channelToLinear( red ) ) + ( 0.7152 * channelToLinear( green ) ) + ( 0.0722 * channelToLinear( blue ) );
	}

	function contrastRatio( first, second ) {
		const lighter = Math.max( luminance( first ), luminance( second ) );
		const darker = Math.min( luminance( first ), luminance( second ) );
		return ( lighter + 0.05 ) / ( darker + 0.05 );
	}

	function backgroundTextRatio() {
		const text = colorValue( 'background_text_color' );
		if ( backgroundType.value === 'gradient' ) {
			return Math.min(
				contrastRatio( text, colorValue( 'gradient_start' ) ),
				contrastRatio( text, colorValue( 'gradient_end' ) )
			);
		}

		return contrastRatio( text, colorValue( 'background_color' ) );
	}

	function updateContrastStatus() {
		const textRatio = contrastRatio( colorValue( 'text_color' ), colorValue( 'panel_color' ) );
		const backgroundRatio = backgroundTextRatio();
		const buttonRatio = contrastRatio( colorValue( 'button_text_color' ), colorValue( 'primary_color' ) );
		const fieldRatio = contrastRatio( colorValue( 'field_text_color' ), colorValue( 'field_background_color' ) );
		const passes = textRatio >= 4.5 && backgroundRatio >= 4.5 && buttonRatio >= 4.5 && fieldRatio >= 4.5;
		contrastStatus.classList.toggle( 'is-good', passes );
		contrastStatus.classList.toggle( 'is-warning', ! passes );
		contrastStatus.textContent = window.FegutogiLoginMood.contrast + ': ' + window.FegutogiLoginMood.textLabel + ' ' + textRatio.toFixed( 2 ) + ':1 · ' + window.FegutogiLoginMood.backgroundLabel + ' ' + backgroundRatio.toFixed( 2 ) + ':1 · ' + window.FegutogiLoginMood.buttonLabel + ' ' + buttonRatio.toFixed( 2 ) + ':1 · ' + window.FegutogiLoginMood.fieldLabel + ' ' + fieldRatio.toFixed( 2 ) + ':1 · ' + ( passes ? window.FegutogiLoginMood.aaPassed : window.FegutogiLoginMood.aaReview );
	}

	function selectMedia( target ) {
		const isLogo = target === 'logo';
		const frame = window.wp.media( {
			title: isLogo ? window.FegutogiLoginMood.chooseLogo : window.FegutogiLoginMood.chooseBackground,
			button: { text: window.FegutogiLoginMood.useImage },
			library: { type: 'image' },
			multiple: false,
		} );

		frame.on( 'select', function () {
			applyMediaAttachment( target, frame.state().get( 'selection' ).first() );
		} );

		frame.open();
	}

	function applyMediaAttachment( target, attachmentModel ) {
		const attachment = attachmentModel && typeof attachmentModel.toJSON === 'function' ? attachmentModel.toJSON() : attachmentModel;
		if ( ! attachment || ! attachment.id || ! attachment.url ) {
			return;
		}
		const imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
		const idInput = document.getElementById( 'loginmood-' + target + '-id' );
		const thumb = document.getElementById( 'loginmood-' + target + '-thumb' );
		const label = thumb.querySelector( '.loginmood-dropzone-label' );

		idInput.value = attachment.id;
		thumb.querySelectorAll( 'img' ).forEach( function ( existingImage ) { existingImage.remove(); } );
		const image = document.createElement( 'img' );
		image.src = imageUrl;
		image.alt = '';
		thumb.prepend( image );
		thumb.classList.add( 'has-media' );

		if ( target === 'logo' ) {
			previewLogo.innerHTML = '';
			const previewImage = image.cloneNode();
			previewImage.addEventListener( 'load', updatePreview );
			previewLogo.appendChild( previewImage );
			if ( label ) {
				label.textContent = window.FegutogiLoginMood.logoDropLabel;
			}
		} else {
			preview.dataset.backgroundImage = attachment.url;
			backgroundType.value = 'image';
			updateBackgroundPanels();
		}

		updatePreview();
	}

	function initLogoUploader() {
		if ( ! logoThumb || ! window.wp || ! window.wp.Uploader || ! window.wp.Uploader.browser.supported ) {
			return;
		}

		const label = logoThumb.querySelector( '.loginmood-dropzone-label' );
		const uploadProxy = document.getElementById( 'loginmood-logo-upload-proxy' );
		const logoUploader = new window.wp.Uploader( {
			container: logoThumb.parentElement,
			browser: uploadProxy,
			plupload: {
				multi_selection: false,
				filters: {
					mime_types: [ { title: 'Images', extensions: 'jpg,jpeg,png,gif,webp,svg' } ],
				},
			},
			init: function () {
				logoThumb.classList.add( 'supports-drag-drop' );
				window.setTimeout( function () {
					logoThumb.parentElement.querySelectorAll( '.moxie-shim input[type="file"]' ).forEach( function ( input ) {
						input.tabIndex = -1;
						input.setAttribute( 'aria-hidden', 'true' );
						const shim = input.closest( '.moxie-shim' );
						if ( shim ) {
							shim.hidden = true;
							shim.setAttribute( 'aria-hidden', 'true' );
						}
					} );
				}, 0 );
			},
			success: function ( attachment ) {
				logoThumb.classList.remove( 'is-uploading' );
				logoThumb.setAttribute( 'aria-busy', 'false' );
				applyMediaAttachment( 'logo', attachment );
			},
			error: function ( message ) {
				logoThumb.classList.remove( 'is-uploading' );
				logoThumb.setAttribute( 'aria-busy', 'false' );
				if ( label ) {
					label.textContent = window.FegutogiLoginMood.logoDropLabel;
				}
				window.alert( message || window.FegutogiLoginMood.logoUploadError );
			},
		} );

		[ 'dragenter', 'dragover' ].forEach( function ( eventName ) {
			logoThumb.addEventListener( eventName, function ( event ) {
				event.preventDefault();
				event.stopPropagation();
				logoThumb.classList.add( 'drag-over' );
			} );
		} );
		logoThumb.addEventListener( 'dragleave', function ( event ) {
			event.preventDefault();
			event.stopPropagation();
			logoThumb.classList.remove( 'drag-over' );
		} );
		logoThumb.addEventListener( 'drop', function ( event ) {
			event.preventDefault();
			event.stopPropagation();
			logoThumb.classList.remove( 'drag-over' );
			const files = event.dataTransfer && event.dataTransfer.files;
			if ( ! files || ! files[0] || ! logoUploader.uploader ) {
				return;
			}
			logoThumb.classList.add( 'is-uploading' );
			logoThumb.setAttribute( 'aria-busy', 'true' );
			if ( label ) {
				label.textContent = window.FegutogiLoginMood.logoUploading;
			}
			logoUploader.uploader.addFile( files[0] );
		} );
	}

	form.addEventListener( 'input', function ( event ) {
		const target = event.target;
		if ( target.classList.contains( 'loginmood-color-value' ) && isHexColor( target.value.trim() ) ) {
			target.dataset.lastColor = target.value.trim().toUpperCase();
			syncPaletteSelect( target.dataset.colorKey, target.dataset.lastColor );
		} else if ( target.classList.contains( 'loginmood-color-picker' ) ) {
			setColor( target.dataset.colorKey, target.value );
		}
		if ( target.dataset.rangeOutput ) {
			document.getElementById( target.dataset.rangeOutput ).textContent = target.value + ' px';
		}
		updatePreview();
	} );
	form.addEventListener( 'change', function ( event ) {
		const target = event.target;
		if ( target.classList.contains( 'loginmood-color-value' ) && ! isHexColor( target.value.trim() ) ) {
			target.value = target.dataset.lastColor;
			updatePreview();
		} else if ( target.classList.contains( 'loginmood-color-value' ) ) {
			target.value = target.value.trim().toUpperCase();
		}
		updatePreview();
	} );

	form.querySelectorAll( '.loginmood-palette-select' ).forEach( function ( select ) {
		select.addEventListener( 'change', function () {
			if ( ! select.value ) {
				return;
			}
			setColor( select.dataset.colorKey, select.value );
		} );
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( event.target.closest( '.loginmood-palette-dropdown' ) ) {
			return;
		}
		document.querySelectorAll( '.loginmood-palette-menu' ).forEach( function ( menu ) { menu.hidden = true; } );
		document.querySelectorAll( '.loginmood-palette-toggle' ).forEach( function ( toggle ) { toggle.setAttribute( 'aria-expanded', 'false' ); } );
	} );
	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key !== 'Escape' ) {
			return;
		}
		document.querySelectorAll( '.loginmood-palette-menu' ).forEach( function ( menu ) { menu.hidden = true; } );
		document.querySelectorAll( '.loginmood-palette-toggle' ).forEach( function ( toggle ) { toggle.setAttribute( 'aria-expanded', 'false' ); } );
	} );

	form.querySelectorAll( '.loginmood-eyedropper' ).forEach( function ( button ) {
		if ( ! window.EyeDropper ) {
			button.hidden = true;
			return;
		}

		button.addEventListener( 'click', function () {
			const eyedropper = new window.EyeDropper();
			eyedropper.open().then( function ( result ) {
				setColor( button.dataset.colorKey, result.sRGBHex );
			} ).catch( function () {
				// Closing the eyedropper without selecting a color is not an error.
			} );
		} );
	} );

	function importPaletteFile( file ) {
		if ( ! file ) {
			return;
		}

		const reader = new FileReader();
		reader.addEventListener( 'load', function () {
			const extension = file.name.split( '.' ).pop().toLowerCase();
			const colors = extension === 'ase' ? extractAseColors( reader.result ) : ( extension === 'csv' ? extractCsvColors( String( reader.result ) ) : extractPaletteColors( String( reader.result ) ) );
			if ( colors.length === 0 ) {
				window.alert( window.FegutogiLoginMood.paletteNoColors );
				return;
			}
			brandPalette = colors;
			renderPaletteControls();
			paletteTable.open = true;
			paletteFile.value = '';
			paletteDropzone.classList.remove( 'is-dragging' );
		} );
		reader.addEventListener( 'error', function () {
			paletteDropzone.classList.remove( 'is-dragging' );
			window.alert( window.FegutogiLoginMood.paletteReadError );
		} );
		if ( file.name.toLowerCase().endsWith( '.ase' ) ) {
			reader.readAsArrayBuffer( file );
		} else {
			reader.readAsText( file );
		}
	}

	paletteFile.addEventListener( 'change', function () {
		importPaletteFile( paletteFile.files[0] );
	} );

	[ 'dragenter', 'dragover' ].forEach( function ( eventName ) {
		paletteDropzone.addEventListener( eventName, function ( event ) {
			event.preventDefault();
			event.stopPropagation();
			paletteDropzone.classList.add( 'is-dragging' );
		} );
	} );
	[ 'dragleave', 'drop' ].forEach( function ( eventName ) {
		paletteDropzone.addEventListener( eventName, function ( event ) {
			event.preventDefault();
			event.stopPropagation();
			paletteDropzone.classList.remove( 'is-dragging' );
		} );
	} );
	paletteDropzone.addEventListener( 'drop', function ( event ) {
		const files = event.dataTransfer && event.dataTransfer.files;
		if ( files && files[0] ) {
			importPaletteFile( files[0] );
		}
	} );

	clearPalette.addEventListener( 'click', function () {
		brandPalette = [];
		paletteTable.open = false;
		renderPaletteControls();
	} );
	backgroundType.addEventListener( 'change', function () {
		updateBackgroundPanels();
		updatePreview();
	} );

	document.querySelectorAll( '.loginmood-select-media' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			selectMedia( button.dataset.target );
		} );
	} );

	document.querySelectorAll( '.loginmood-remove-media' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			const target = button.dataset.target;
			document.getElementById( 'loginmood-' + target + '-id' ).value = '';
			const thumb = document.getElementById( 'loginmood-' + target + '-thumb' );
			thumb.querySelectorAll( 'img' ).forEach( function ( image ) { image.remove(); } );
			thumb.classList.remove( 'has-media' );
			if ( target === 'logo' ) {
				previewLogo.innerHTML = '<span>WordPress</span>';
			} else {
				preview.dataset.backgroundImage = '';
			}
			updatePreview();
		} );
	} );

	document.querySelectorAll( '[data-loginmood-preset]' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			const preset = presets[ button.dataset.loginmoodPreset ];
			Object.keys( preset ).forEach( function ( key ) {
				field( key ).value = preset[ key ];
				if ( field( key ).dataset.rangeOutput ) {
					document.getElementById( field( key ).dataset.rangeOutput ).textContent = preset[ key ] + ' px';
				}
				if ( isHexColor( String( preset[ key ] ) ) ) {
					field( key ).dataset.lastColor = preset[ key ];
					syncPaletteSelect( key, preset[ key ] );
				}
			} );
			backgroundType.value = 'color';
			updateBackgroundPanels();
			updatePreview();
		} );
	} );

	document.querySelectorAll( '.loginmood-reset-link' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( event ) {
			if ( ! window.confirm( window.FegutogiLoginMood.resetConfirm ) ) {
				event.preventDefault();
			}
		} );
	} );

	const existingLogo = previewLogo.querySelector( 'img' );
	if ( existingLogo ) {
		existingLogo.addEventListener( 'load', updatePreview );
	}

	renderPaletteControls();
	initLogoUploader();
	updateBackgroundPanels();
	updatePreview();
}() );
