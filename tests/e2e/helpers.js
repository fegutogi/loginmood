import { expect } from '@playwright/test';

export async function openLogin( page, path = '/wp-login.php' ) {
	await page.goto( path );
	await expect( page.locator( 'body' ) ).toHaveClass( /loginmood-login/ );
	if ( path === '/wp-login.php' ) {
		await expect( page.locator( '.wp-hide-pw' ) ).toBeVisible();
		await page.waitForTimeout( 150 );
	}
}

export async function fillLoginForm( page, username, password ) {
	await page.locator( '#user_login' ).fill( username );
	await page.locator( '#user_pass' ).fill( password );
	await expect( page.locator( '#user_login' ) ).toHaveValue( username );
	await expect( page.locator( '#user_pass' ) ).toHaveValue( password );
}

export async function loginAsAdmin( page ) {
	await openLogin( page );
	await fillLoginForm( page, 'admin', 'password' );
	await page.locator( '#wp-submit' ).click();
	await expect( page.locator( '#wpadminbar' ) ).toBeVisible();
}

export async function openSettings( page ) {
	await loginAsAdmin( page );
	await page.goto( '/wp-admin/options-general.php?page=loginmood' );
	await expect( page.getByRole( 'heading', { name: 'LoginMood', exact: true } ) ).toBeVisible();
}

export function cssNumber( value ) {
	return Number.parseFloat( String( value ).replace( 'px', '' ) );
}
