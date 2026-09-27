{
  description = "nxu.hu";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs = { nixpkgs, flake-utils, ... }:
    flake-utils.lib.eachSystem [ "aarch64-darwin" "x86_64-linux" ] (system:
      let
        pkgs = nixpkgs.legacyPackages.${system};
      in
      {
        devShells.default = pkgs.mkShell {
          packages = [ pkgs.bun pkgs.rsync ];

          # sharp's prebuilt Linux binary links against libstdc++, which Nix's loader can't find in /usr/lib.
          LD_LIBRARY_PATH = pkgs.lib.optionalString pkgs.stdenv.isLinux
            (pkgs.lib.makeLibraryPath [ pkgs.stdenv.cc.cc.lib ]);
        };
      });
}
