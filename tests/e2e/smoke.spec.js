import { test, expect } from '@playwright/test';
import { openSettings, cssNumber } from './helpers.js';

test( 'plugin activates and styles the login', async ( { page } ) => {
	const consoleErrors = [];
	page.on( 'console', ( message ) => {
		if ( message.type() === 'error' ) consoleErrors.push( message.text() );
	} );
	await page.goto( '/wp-login.php' );
	await expect( page.locator( 'body' ) ).toHaveClass( /loginmood-login/ );
	await expect( page.locator( 'link#loginmood-css' ) ).toHaveAttribute( 'href', /loginmood\/assets\/css\/login\.css\?ver=1\.0\.0-rc\.3/ );
	const radius = await page.locator( 'html' ).evaluate( ( element ) => getComputedStyle( element ).getPropertyValue( '--loginmood-radius' ) );
	const padding = await page.locator( 'html' ).evaluate( ( element ) => getComputedStyle( element ).getPropertyValue( '--loginmood-message-padding' ) );
	expect( cssNumber( radius ) ).toBe( 12 );
	expect( cssNumber( padding ) ).toBe( 23 );
	expect( consoleErrors ).toEqual( [] );
} );

test( 'administration screen opens with the current identity', async ( { page } ) => {
	await openSettings( page );
	await expect( page ).toHaveTitle( /LoginMood/ );
	await expect( page.locator( '#loginmood-settings-form' ) ).toBeVisible();
	await expect( page.getByText( /CSV.*Adobe ASE/ ) ).toBeVisible();
} );
