import { test, expect } from '@playwright/test';
import { cssNumber, fillLoginForm, openLogin } from './helpers.js';

const pluginName = process.env.LOGINMOOD_COMPAT_NAME || 'Compatibility plugin';

test.describe( `${ pluginName } compatibility`, () => {
	test( 'keeps the branded native login available', async ( { page } ) => {
		const consoleErrors = [];
		page.on( 'console', ( message ) => {
			if ( message.type() === 'error' ) {
				consoleErrors.push( message.text() );
			}
		} );
		await openLogin( page );
		await expect( page.locator( 'link[href*="loginmood/assets/css/login.css"][href*="1.0.0-rc.3"]' ) ).toHaveCount( 1 );
		await expect( page.locator( '#loginform' ) ).toBeVisible();
		expect( consoleErrors ).toEqual( [] );
	} );

	test( 'preserves the branded invalid-login error', async ( { page } ) => {
		await openLogin( page );
		await fillLoginForm( page, 'loginmood-compat-invalid', 'invalid-password' );
		await page.locator( '#wp-submit' ).click();
		const error = page.locator( '#login_error' );
		await expect( error ).toBeVisible();
		await expect( page.locator( 'body' ) ).toHaveClass( /loginmood-login/ );
		const styles = await error.evaluate( ( element ) => {
			const computed = getComputedStyle( element );
			return { border: computed.borderLeftWidth, padding: computed.paddingLeft };
		} );
		expect( styles.border ).toBe( '0px' );
		expect( cssNumber( styles.padding ) ).toBeGreaterThanOrEqual( 20 );
	} );

	test( 'preserves the branded password-recovery screen', async ( { page } ) => {
		await openLogin( page, '/wp-login.php?action=lostpassword' );
		await expect( page.locator( '.message' ) ).toBeVisible();
		await expect( page.locator( '#lostpasswordform' ) ).toBeVisible();
	} );

	test( 'allows an administrator login or an authenticated setup redirect', async ( { page } ) => {
		await openLogin( page );
		await fillLoginForm( page, 'admin', 'password' );
		await page.locator( '#wp-submit' ).click();
		await page.waitForLoadState( 'domcontentloaded' );
		await expect( page ).not.toHaveURL( /wp-login\.php(?:\?|$)/ );
		await expect( page.locator( 'body' ) ).not.toContainText( /critical error|fatal error|uncaught error/i );
	} );
} );
