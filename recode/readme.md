# SWHLabPHP v2

The original SWHLabPHP (v1) project was a proof of concept. It morphed greatly with time, but is hapazardly held together and is brittle. A from-scratch re-code would improve its operation, stability, expandability, and flexibility. This is a collection of notes indicating how to improve the project in the future

* no hard paths -- define `$PROJECT_ROOT` in config.php and reference all data folders with respect to this root folder. This locally becomes `D:\X_Drive\Data\` but is displayed as `X:\Data\`
