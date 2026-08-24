import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { test, expect } from '@playwright/test';
import { openSettings } from './helpers.js';

const fixtures = path.resolve( path.dirname( fileURLToPath( import.meta.url ) ), '../fixtures' );

function rgbAseSwatch( name, red, green, blue ) {
	const nameLength = name.length + 1;
	const blockLength = 2 + ( nameLength * 2 ) + 4 + 12 + 2;
	const buffer = Buffer.alloc( 12 + 6 + blockLength );
	buffer.write( 'ASEF', 0, 'ascii' );
	buffer.writeUInt16BE( 1, 4 );
	buffer.writeUInt16BE( 0, 6 );
	buffer.writeUInt32BE( 1, 8 );
	buffer.writeUInt16BE( 0x0001, 12 );
	buffer.writeUInt32BE( blockLength, 14 );
	buffer.writeUInt16BE( nameLength, 18 );
	for ( let index = 0; index < name.length; index++ ) {
		buffer.writeUInt16BE( name.charCodeAt( index ), 20 + ( index * 2 ) );
	}
	const modelOffset = 20 + ( nameLength * 2 );
	buffer.write( 'RGB ', modelOffset, 'ascii' );
	buffer.writeFloatBE( red / 255, modelOffset + 4 );
	buffer.writeFloatBE( green / 255, modelOffset + 8 );
	buffer.writeFloatBE( blue / 255, modelOffset + 12 );
	buffer.writeUInt16BE( 0, modelOffset + 16 );
	return buffer;
}

test( 'CSV palettes preserve optional names and render real color dots', async ( { page } ) => {
	await openSettings( page );
	await page.locator( '#loginmood-palette-file' ).setInputFiles( path.join( fixtures, 'brand-palette.csv' ) );
	await expect( page.locator( '#loginmood-palette-swatches' ) ).toContainText( '3' );

	const panelField = page.locator( '.loginmood-color-field' ).filter( { has: page.locator( 'label', { hasText: /^Panel$/ } ) } );
	await panelField.locator( '.loginmood-palette-toggle' ).click();
	const options = panelField.locator( '.loginmood-palette-option' );
	await expect( options ).toHaveCount( 3 );
	await expect( options.nth( 0 ) ).toContainText( 'Azul institucional · #1259A7' );
	await expect( options.nth( 1 ) ).toContainText( '#F4C542' );
	await expect( options.nth( 1 ) ).not.toContainText( '·' );
	await expect( options.nth( 2 ) ).toContainText( 'Rojo campaña · #D93A3A' );

	const dots = await options.locator( '.loginmood-palette-dot' ).evaluateAll( ( elements ) => elements.map( ( element ) => getComputedStyle( element ).backgroundColor ) );
	expect( dots ).toEqual( [ 'rgb(18, 89, 167)', 'rgb(244, 197, 66)', 'rgb(217, 58, 58)' ] );

	await options.nth( 0 ).click();
	await expect( page.locator( '#loginmood-panel-color' ) ).toHaveValue( '#1259A7' );
	await expect( page.locator( '.loginmood-preview-form' ) ).toHaveCSS( 'background-color', 'rgb(18, 89, 167)' );

	const table = page.locator( '#loginmood-palette-table' );
	await expect( table ).toBeVisible();
	await expect( table.locator( 'tbody tr' ) ).toHaveCount( 3 );
	await expect( table.locator( 'tbody tr' ).nth( 0 ) ).toContainText( '#F4C542' );
	await expect( table.locator( 'tbody tr' ).nth( 1 ) ).toContainText( 'Azul institucional' );
	await expect( table.locator( 'tbody tr' ).nth( 2 ) ).toContainText( 'Rojo campaña' );
} );

test( 'a palette file can be dropped and opens the ordered table', async ( { page } ) => {
	await openSettings( page );
	const transfer = await page.evaluateHandle( () => {
		const dataTransfer = new DataTransfer();
		dataTransfer.items.add( new File( [ 'name,hex\nZeta,#445566\nAlfa,#112233' ], 'dropped-palette.csv', { type: 'text/csv' } ) );
		return dataTransfer;
	} );
	const dropzone = page.locator( '#loginmood-palette-dropzone' );
	await dropzone.dispatchEvent( 'dragenter', { dataTransfer: transfer } );
	await expect( dropzone ).toHaveClass( /is-dragging/ );
	await dropzone.dispatchEvent( 'drop', { dataTransfer: transfer } );
	await expect( page.locator( '#loginmood-brand-palette' ) ).toHaveValue( /Alfa/ );
	await expect( page.locator( '#loginmood-palette-table' ) ).toHaveAttribute( 'open', '' );
	await expect( page.locator( '#loginmood-palette-table tbody tr' ).nth( 0 ) ).toContainText( 'Alfa' );
	await expect( page.locator( '#loginmood-palette-table tbody tr' ).nth( 1 ) ).toContainText( 'Zeta' );
} );

test( 'a logo can be dropped into the native WordPress uploader', async ( { page } ) => {
	await openSettings( page );
	const transfer = await page.evaluateHandle( () => {
		const binary = atob( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Z4kQAAAAASUVORK5CYII=' );
		const bytes = Uint8Array.from( binary, ( character ) => character.charCodeAt( 0 ) );
		const dataTransfer = new DataTransfer();
		dataTransfer.items.add( new File( [ bytes ], 'loginmood-drop-test.png', { type: 'image/png' } ) );
		return dataTransfer;
	} );
	const dropzone = page.locator( '#loginmood-logo-thumb' );
	await expect( dropzone ).toHaveClass( /supports-drag-drop/ );
	await dropzone.dispatchEvent( 'dragover', { dataTransfer: transfer } );
	await dropzone.dispatchEvent( 'drop', { dataTransfer: transfer } );
	await expect( page.locator( '#loginmood-logo-id' ) ).not.toHaveValue( /^(?:0)?$/, { timeout: 15000 } );
	await expect( dropzone.locator( 'img' ) ).toHaveCount( 1 );
	await expect( dropzone.locator( 'img' ) ).toHaveAttribute( 'src', /loginmood-drop-test/ );
	await expect( page.locator( '.loginmood-logo-gap-option' ) ).toBeVisible();
	await expect( page.locator( '#loginmood-logo-panel-gap' ) ).toHaveAttribute( 'max', '80' );
} );

test( 'Adobe ASE palettes preserve the swatch name and RGB color', async ( { page } ) => {
	await openSettings( page );
	await page.locator( '#loginmood-palette-file' ).setInputFiles( {
		name: 'brand-palette.ase',
		mimeType: 'application/octet-stream',
		buffer: rgbAseSwatch( 'Azul ASE', 18, 89, 167 ),
	} );
	await expect( page.locator( '#loginmood-brand-palette' ) ).toHaveValue( /Azul ASE/ );
	await expect( page.locator( '.loginmood-palette-option' ).first() ).toContainText( 'Azul ASE · #1259A7' );
} );

const namedFixtures = {
	'brand-palette.css': 'azul-institucional',
	'brand-palette.json': 'Azul institucional',
	'brand-palette.gpl': 'Azul institucional',
};

for ( const [ fixture, expectedName ] of Object.entries( namedFixtures ) ) {
	test( `${ fixture } imports valid named colors`, async ( { page } ) => {
		await openSettings( page );
		await page.locator( '#loginmood-palette-file' ).setInputFiles( path.join( fixtures, fixture ) );
		await expect( page.locator( '#loginmood-brand-palette' ) ).toHaveValue( new RegExp( expectedName ) );
		await expect( page.locator( '.loginmood-palette-option' ).first() ).toContainText( '#1259A7' );
	} );
}
