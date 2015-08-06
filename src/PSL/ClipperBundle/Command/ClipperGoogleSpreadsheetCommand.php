<?php
/**
 * PSL/ClipperBundle/Command/ClipperGoogleSpreadsheetCommand.php
 *
 * Google Spreadsheet Cache Console Command
 *
 * @version 1.0
 * @date 2015-07-29
 */

namespace PSL\ClipperBundle\Command;

use \Exception as Exception;
use \stdClass as stdClass;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ClipperGoogleSpreadsheetCommand extends ContainerAwareCommand {
  protected function configure() {
    $this->setName('clipper:gdoc-auth-refresh')
         ->setDescription('Google Document Authentication cache refresh')
         ->addOption('force', 'f', InputOption::VALUE_NONE, 'First clear cache then re-authenticate.');;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $timestamp = microtime(TRUE);
    $this->logger = $this->getContainer()->get('monolog.logger.clipper');

    $force = $input->getOption('force');
    $gsc   = $this->getContainer()->get('google_spreadsheet');
    $cc    = $this->getContainer()->get('clipper_cache');

    if (!$cc->is_enabled()) {
      $this->logger->warning("ClipperCache is disabled.");
    }
    elseif ($force) {
      $key = $gsc->get_auth_cache_key();
      if ($cc->is_enabled()) {
        $res = $cc->delete($key);
        $this->logger->info(($res ? 'Cache token has been' : 'There were no cache token' ) . ' removed.');
      }
    }
    elseif ($cc->is_enabled()) {
      $this->logger->info('ClipperCache is enabled.');
    }

    $service = $gsc->setupFeasibilitySheet();
    $this->logger->info('gDoc Auth complete.', array('log' => $service->last_messages));

    $token_active = $service->validate_token_expiry();
    if (!empty($token_active)) {
      $this->logger->info('Success: Access token is active.');
    }
    else {
      $this->logger->error('Success: There seems to be an issue during the authentication.');
    }

    $timestamp = (microtime(TRUE) - $timestamp);
    $timestamp = number_format($timestamp, 4, '.', ',');
    $this->logger->info('Console completed', array('timed-secs', $timestamp));
  }
}