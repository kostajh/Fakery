<?php

use MediaWiki\MediaWikiServices;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";
// @todo this shouldn't be necessary
require_once "$IP/extensions/Fakery/vendor/fzaninotto/faker/src/autoload.php";

class GeneratePages extends Maintenance {

	/**
	 * Execute the script.
	 *
	 * @return bool
	 */
	public function execute() {
		$count = (int)$this->getOption( 'count' );
		$this->output( sprintf( "Generating %d Pages ...\n", $count ) );
		$titles = [];
		$user_string = $this->getOption( 'user' );
		$user = $user_string ? User::newFromName( $user_string ) :
			User::newFromName( 'Admin' );

		// Generate Pages, edits, and add to watchlist.
		for ( $i = 0; $i < $count; $i++ ) {
			try {
				$title = $this->generatePage();
				if ( $title ) {
					$this->generateEditsOnPage( $title );
				}
				$titles[] = $title;
				$this->addTitleToUserWatchlist( $title, $user );

			}
			catch ( MWException $exception ) {
				$this->output( sprintf( 'Exception: %s', $exception->getMessage() ) );
				$this->output( $exception->getTraceAsString() );
				return false;
			}
		}
		return true;
	}

	/**
	 * Add a title to the user's watchlist.
	 *
	 * @param Title $title
	 * @param User $user
	 */
	private function addTitleToUserWatchlist( Title $title, User $user ) {
		MediaWikiServices::getInstance()->getWatchedItemStore()->addWatch(
			$user,
			$title
		);
		$this->output( sprintf( "Added %s page to %s's watchlist.\n", $title->getTitleValue(),
			$user->getName()
		) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Generate pages, with edits, and add them to a user watchlist.' );
		$this->addOption(
			'count',
			'The number of Pages to create',
			true,
			false,
			'c'
		);
		$this->addOption(
			'user',
			"Add the generated pages to this user's watchlist",
			true,
			false,
			'u'
		);
	}

	/**
	 * Generate a page with random text.
	 *
	 * @return null|Title
	 * @throws MWException
	 */
	private function generatePage() {
		$faker = Faker\Factory::create();
		$title = Title::newFromText( $faker->name );
		$page = WikiPage::factory( $title );
		$user = User::newFromName( $faker->name );
		$updater = $page->newPageUpdater( $user );
		$content = new TextContent( $faker->paragraph );
		$updater->setContent( 'main', $content );
		$comment = new CommentStoreComment( null,  $faker->sentence );
		$updater->saveRevision( $comment );
		if ( !$updater->wasSuccessful() ) {
			throw new MWException( 'Unable to create a page.' );
		}
		$this->output( sprintf( "Created page %s\n",
			$title->getTitleValue() ) );
		return $title;
	}

	/**
	 * Generate edits for a Title.
	 *
	 * @param Title $title
	 * @return bool
	 * @throws MWException
	 */
	private function generateEditsOnPage( Title $title ) {
		$faker = Faker\Factory::create();
		$page = WikiPage::factory( $title );
		$user = User::newFromName( $faker->name );
		$updater = $page->newPageUpdater( $user );
		$newContent = new TextContent( $faker->paragraph );
		$comment = new CommentStoreComment( null, $faker->sentence );
		$updater->setContent( 'main', $newContent );
		$updater->saveRevision( $comment, EDIT_UPDATE );
		if ( !$updater->wasSuccessful() ) {
			throw new MWException( 'Failed to save an edit to the page.' );
		}
		return true;
	}
}

$maintClass = GeneratePages::class;
require_once RUN_MAINTENANCE_IF_MAIN;
