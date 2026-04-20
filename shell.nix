let
	nixpkgs = fetchTarball "https://github.com/NixOS/nixpkgs/tarball/nixos-unstable";
	pkgs = import nixpkgs { config = {}; overlays = []; };
in

pkgs.mkShellNoCC {
	packages = with pkgs; [
		git
		php85
		phpPackages.composer
		nodejs_24
		
		(pkgs.writeShellScriptBin "sail" ''
			if [ -f sail ]; then
				exec bash sail "$@"
			else
				exec bash vendor/bin/sail "$@"
			fi
		'')
	];
}