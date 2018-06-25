# Fakery

Fakery is an extension for populating a MediaWiki database with fake content, courtesy of [faker](https://github.com/fzaninotto/faker).

For now, nothing is generic or abstracted about this nor is it very sophisticated; this extension has a script to assist in debugging [T197168](https://phabricator.wikimedia.org/T197168).

## Installation

Copy this extension into `extensions` and run `composer install`.

## Usage

`mwscript extensions/Fakery/maintenace/GeneratePages.php --count=5000 --user=Admin` to generate 5,000 pages with edits, and add them to the `Admin` user's watchlist.
