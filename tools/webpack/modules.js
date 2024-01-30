/**
 * WordPress dependencies
 */
const DependencyExtractionPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

/**
 * Internal dependencies
 */
const {
	baseDir,
	getBaseConfig,
	normalizeJoin,
	MODULES,
	WORDPRESS_NAMESPACE,
} = require( './shared' );

module.exports = function (
	env = { environment: 'production', watch: false, buildTarget: false }
) {
	const mode = env.environment;
	const suffix = mode === 'production' ? '.min' : '';
	let buildTarget = env.buildTarget
		? env.buildTarget
		: mode === 'production'
		? 'build'
		: 'src';
	buildTarget = buildTarget + '/wp-includes';

	const baseConfig = getBaseConfig( env );
	const config = {
		...baseConfig,
		entry: MODULES.map( ( packageName ) =>
			packageName.replace( WORDPRESS_NAMESPACE, '' )
		).reduce( ( memo, packageName ) => {
			memo[ packageName ] = {
				import: normalizeJoin(
					baseDir,
					// Todo, remove the `/src/index.js` part once the build-modules output
					// of these packages is properly built.
					`node_modules/@wordpress/${ packageName }/src/index.js`
				),
			};

			return memo;
		}, {} ),
		experiments: {
			outputModule: true,
		},
		output: {
			devtoolNamespace: 'wp',
			filename: `[name]${ suffix }.js`,
			path: normalizeJoin( baseDir, `${ buildTarget }/js/dist` ),
			library: {
				type: 'module',
			},
			environment: { module: true },
		},
		externalsType: 'module',
		externals: {
			// Todo, use the MODULES constant instead.
			'@wordpress/interactivity': '@wordpress/interactivity',
			'@wordpress/interactivity-router':
				'import @wordpress/interactivity-router',
		},
		resolve: {
			// Todo, remove once the build-modules output of these packages is
			// properly built.
			extensions: [ '.js', '.ts', '.tsx' ],
		},
		module: {
			rules: [
				{
					test: /\.(j|t)sx?$/,
					use: [
						{
							loader: require.resolve( 'babel-loader' ),
							options: {
								cacheDirectory:
									process.env.BABEL_CACHE_DIRECTORY || true,
								babelrc: false,
								configFile: false,
								presets: [
									// Todo, remove once the build-modules output of these
									// packages is properly built.
									'@babel/preset-typescript',
									[
										'@babel/preset-react',
										{
											runtime: 'automatic',
											importSource: 'preact',
										},
									],
								],
							},
						},
					],
				},
			],
		},
		plugins: [
			...baseConfig.plugins,
			new DependencyExtractionPlugin( {
				injectPolyfill: false,
				useDefaults: false,
			} ),
		],
	};

	return config;
};
