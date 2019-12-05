const fs = require( "fs" );

/**
 * Grunt task to bump the RC version.
 *
 * @param {Object} grunt The grunt helper object.
 *
 * @returns {void}
 */
module.exports = function( grunt ) {
	grunt.registerTask(
		"bump-rc-version",
		"Bumps the versions to the next RC",
		function() {
			let pluginVersionFlag = grunt.option( "plugin-version" );

			// Retrieve the current plugin version from package.json.
			const packageJson = JSON.parse( fs.readFileSync( "package.json", "utf8" ) ) || {};
			const pluginVersionPackageJson = packageJson.yoast.pluginVersion;

			// Strip off the RC part from the current plugin version.
			const parsedVersion = pluginVersionPackageJson.split( "-RC" );

			// From the resulting array, get the first value (the second value is the RC number).
			const strippedVersion = parsedVersion[ 0 ];

			// If the flagged version matches the version in package.json, increment the RC version.
			if ( pluginVersionFlag === strippedVersion ) {
				const currentRCVersion = parsedVersion[ 1 ] ? parsedVersion[ 1 ] : "0";
				const bumpedRCVersion = parseInt( currentRCVersion, 10 ) + 1;
				pluginVersionFlag = pluginVersionFlag + "-RC" + bumpedRCVersion;
			}

			// Else, the RC is 1.
			else {
				pluginVersionFlag = pluginVersionFlag + "-RC1";
			}

			// eslint-disable-next-line no-console
			console.log( "Bumped the plugin version to ".concat( pluginVersionFlag ).concat( "." ) );
		}
	);
};