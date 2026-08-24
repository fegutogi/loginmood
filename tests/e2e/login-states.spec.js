import { test, expect } from '@playwright/test';
import { cssNumber, fillLoginForm, openLogin, openSettings } from './helpers.js';

async function expectComfortableMessage( locator ) {
	await expect( locator ).toBeVisible();
	const styles = await locator.evaluate( ( element ) => {
		const computed = getComputedStyle( element );
		return {
			borderLeftWidth: computed.borderLeftWidth,
			paddingTop: computed.paddingTop,
			paddingRight: computed.paddingRight,
			paddingBottom: computed.paddingBottom,
			paddingLeft: computed.paddingLeft,
		};
	} );
	expect( styles.borderLeftWidth ).toBe( '0px' );
	for ( const side of [ 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft' ] ) {
		expect( cssNumber( styles[side] ) ).toBeGreaterThanOrEqual( 20 );
	}
}

test( 'invalid credentials use a padded error panel', async ( { page } ) => {
	await openLogin( page );
	await fillLoginForm( page, 'loginmood-invalid-user', 'invalid-password' );
	await page.locator( '#wp-submit' ).click();
	await expectComfortableMessage( page.locator( '#login_error' ) );
} );

test( 'lost-password instructions use the same spacing', async ( { page } ) => {
	await openLogin( page, '/wp-login.php?action=lostpassword' );
	await expectComfortableMessage( page.locator( '.message' ) );
} );

test( 'message padding grows with the panel radius without exceeding 32px', async ( { page, context } ) => {
	await openSettings( page );
	await page.locator( '#loginmood-border-radius' ).evaluate( ( input ) => {
		input.value = '50';
		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	} );
	await page.getByRole( 'button', { name: 'Save changes' } ).click();
	await expect( page.locator( '#loginmood-border-radius' ) ).toHaveValue( '50' );

	await context.clearCookies();
	await page.goto( '/wp-login.php?action=lostpassword' );
	const message = page.locator( '.message' );
	await expectComfortableMessage( message );
	await expect( message ).toHaveCSS( 'padding-left', '32px' );
	await expect( message ).toHaveCSS( 'padding-right', '32px' );

	await openSettings( page );
	await page.locator( '#loginmood-border-radius' ).evaluate( ( input ) => {
		input.value = '12';
		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	} );
	await page.getByRole( 'button', { name: 'Save changes' } ).click();
	await expect( page.locator( '#loginmood-border-radius' ) ).toHaveValue( '12' );
} );

test( 'an unauthenticated admin request reaches reauthentication immediately', async ( { page, context } ) => {
	await context.clearCookies();
	await page.goto( '/wp-admin/' );
	await expect( page ).toHaveURL( /wp-login\.php.*reauth=1/ );
	await expect( page.locator( 'body' ) ).toHaveClass( /loginmood-login/ );
} );
