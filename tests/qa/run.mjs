import { spawn } from 'node:child_process';
import { setTimeout as wait } from 'node:timers/promises';
import process from 'node:process';

const profile = process.argv[2] || 'quick';
const updateSnapshots = process.argv.includes( '--update-snapshots' );
const cli = './node_modules/.bin/wp-playground-cli';
const playwright = './node_modules/.bin/playwright';

const stable = { php: '8.3', wp: 'latest' };
const jobs = {
	quick: [ { ...stable, tests: [ 'tests/e2e/smoke.spec.js', 'tests/e2e/login-states.spec.js', 'tests/e2e/palette.spec.js' ], projects: [ 'desktop-chromium' ] } ],
	visual: [ { ...stable, tests: [ 'tests/e2e/visual.spec.js' ], projects: [ 'desktop-chromium', 'mobile-chromium' ] } ],
	matrix: [
		{ php: '7.4', wp: '6.4', tests: [ 'tests/e2e/smoke.spec.js' ], projects: [ 'desktop-chromium' ] },
		{ php: '8.3', wp: 'latest', tests: [ 'tests/e2e/smoke.spec.js' ], projects: [ 'desktop-chromium' ] },
		{ php: '8.5', wp: 'latest', tests: [ 'tests/e2e/smoke.spec.js' ], projects: [ 'desktop-chromium' ] },
		{ php: '8.4', wp: 'nightly', tests: [ 'tests/e2e/smoke.spec.js' ], projects: [ 'desktop-chromium' ] },
	],
	compat: [
		{ ...stable, blueprint: 'tests/blueprints/wp-2fa.json', compatName: 'WP 2FA', tests: [ 'tests/e2e/compatibility.spec.js' ], projects: [ 'desktop-chromium' ] },
		{ ...stable, blueprint: 'tests/blueprints/simple-cloudflare-turnstile.json', compatName: 'Simple Cloudflare Turnstile', tests: [ 'tests/e2e/compatibility.spec.js' ], projects: [ 'desktop-chromium' ] },
		{ ...stable, blueprint: 'tests/blueprints/woocommerce.json', compatName: 'WooCommerce', tests: [ 'tests/e2e/compatibility.spec.js' ], projects: [ 'desktop-chromium' ] },
		{ ...stable, blueprint: 'tests/blueprints/paid-memberships-pro.json', compatName: 'Paid Memberships Pro', tests: [ 'tests/e2e/compatibility.spec.js' ], projects: [ 'desktop-chromium' ] },
	],
	release: [
		{ ...stable, tests: [ 'tests/e2e/smoke.spec.js', 'tests/e2e/login-states.spec.js', 'tests/e2e/palette.spec.js' ], projects: [ 'desktop-chromium', 'firefox', 'webkit' ] },
		{ ...stable, tests: [ 'tests/e2e/visual.spec.js' ], projects: [ 'desktop-chromium', 'mobile-chromium' ] },
		{ php: '7.4', wp: '6.4', tests: [ 'tests/e2e/smoke.spec.js' ], projects: [ 'desktop-chromium' ] },
		{ php: '8.5', wp: 'latest', tests: [ 'tests/e2e/smoke.spec.js' ], projects: [ 'desktop-chromium' ] },
		{ php: '8.4', wp: 'nightly', tests: [ 'tests/e2e/smoke.spec.js' ], projects: [ 'desktop-chromium' ] },
	],
};

if ( ! jobs[profile] ) {
	throw new Error( `Unknown QA profile: ${ profile }` );
}

function runCommand( command, args, env = process.env ) {
	return new Promise( ( resolve, reject ) => {
		const child = spawn( command, args, { stdio: 'inherit', env } );
		child.on( 'error', reject );
		child.on( 'exit', ( code ) => code === 0 ? resolve() : reject( new Error( `${ command } exited with ${ code }` ) ) );
	} );
}

async function waitForServer( url, child ) {
	let consecutiveReadyResponses = 0;
	for ( let attempt = 0; attempt < 120; attempt++ ) {
		if ( child.exitCode !== null ) {
			throw new Error( `Playground stopped before becoming ready (${ child.exitCode })` );
		}
		try {
			const response = await fetch( `${ url }/wp-login.php`, { redirect: 'manual' } );
			const html = await response.text();
			if ( response.status > 0 && response.status < 500 && html.includes( 'loginmood-login' ) && html.includes( '1.0.0-rc.3' ) ) {
				consecutiveReadyResponses++;
				if ( consecutiveReadyResponses >= 8 ) {
					await wait( 1000 );
					return;
				}
			} else {
				consecutiveReadyResponses = 0;
			}
		} catch ( error ) {
			// The server is still booting.
		}
		await wait( 250 );
	}
	throw new Error( `Timed out waiting for ${ url }` );
}

function playgroundReady( child ) {
	return new Promise( ( resolve, reject ) => {
		let output = '';
		const onData = ( chunk, destination ) => {
			const text = chunk.toString();
			destination.write( text );
			output += text;
			if ( output.includes( 'Ready!' ) ) {
				resolve();
			}
		};
		child.stdout.on( 'data', ( chunk ) => onData( chunk, process.stdout ) );
		child.stderr.on( 'data', ( chunk ) => onData( chunk, process.stderr ) );
		child.once( 'error', reject );
		child.once( 'exit', ( code ) => {
			if ( ! output.includes( 'Ready!' ) ) {
				reject( new Error( `Playground exited before readiness (${ code })` ) );
			}
		} );
	} );
}

async function stopServer( child ) {
	if ( child.exitCode !== null ) {
		return;
	}
	try {
		process.kill( -child.pid, 'SIGINT' );
	} catch ( error ) {
		child.kill( 'SIGINT' );
	}
	await wait( 1500 );
	if ( child.exitCode === null ) {
		try {
			process.kill( -child.pid, 'SIGTERM' );
		} catch ( error ) {
			child.kill( 'SIGTERM' );
		}
	}
	child.stdout.destroy();
	child.stderr.destroy();
}

for ( let index = 0; index < jobs[profile].length; index++ ) {
	const job = jobs[profile][index];
	const port = 9400 + index;
	const baseURL = `http://127.0.0.1:${ port }`;
	process.stdout.write( `\nLoginMood QA: WordPress ${ job.wp }, PHP ${ job.php }\n` );
	const server = spawn( cli, [
		'start',
		'--path=.',
		`--php=${ job.php }`,
		`--wp=${ job.wp }`,
		`--port=${ port }`,
		'--skip-browser',
		'--reset',
		'--quiet',
		'--no-login',
		...( job.blueprint ? [ `--blueprint=${ job.blueprint }` ] : [] ),
	], { detached: true, stdio: [ 'ignore', 'pipe', 'pipe' ] } );
	const ready = playgroundReady( server );

	try {
		await Promise.race( [ ready, wait( 120000 ).then( () => { throw new Error( 'Timed out waiting for Playground startup' ); } ) ] );
		await waitForServer( baseURL, server );
		const args = [ 'test', ...job.tests ];
		job.projects.forEach( ( project ) => args.push( `--project=${ project }` ) );
		if ( updateSnapshots ) {
			args.push( '--update-snapshots' );
		}
		await runCommand( playwright, args, { ...process.env, LOGINMOOD_QA_BASE_URL: baseURL, LOGINMOOD_COMPAT_NAME: job.compatName || '' } );
	} finally {
		await stopServer( server );
	}
}

process.exit( 0 );
