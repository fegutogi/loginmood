import { test, expect } from '@playwright/test';
import { fillLoginForm, openLogin } from './helpers.js';

test.use( { reducedMotion: 'reduce' } );

async function settleVisualState( page ) {
	await page.evaluate( () => document.activeElement?.blur() );
	await page.waitForTimeout( 100 );
}

test( 'login panel visual reference', async ( { page } ) => {
	await openLogin( page );
	await settleVisualState( page );
	await expect( page.locator( '#login' ) ).toHaveScreenshot( 'login-panel.png', { animations: 'disabled' } );
} );

test( 'error panel visual reference', async ( { page } ) => {
	await openLogin( page );
	await fillLoginForm( page, 'loginmood-invalid-user', 'invalid-password' );
	await page.locator( '#wp-submit' ).click();
	await settleVisualState( page );
	await expect( page.locator( '#login' ) ).toHaveScreenshot( 'login-error.png', { animations: 'disabled' } );
} );

test( 'lost-password panel visual reference', async ( { page } ) => {
	await openLogin( page, '/wp-login.php?action=lostpassword' );
	await settleVisualState( page );
	await expect( page.locator( '#login' ) ).toHaveScreenshot( 'lost-password.png', { animations: 'disabled' } );
} );
