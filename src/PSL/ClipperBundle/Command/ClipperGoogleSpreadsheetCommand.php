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

    $force = $input->getOption('force');
    $gsc   = $this->getContainer()->get('google_spreadsheet');

    if ($force) {
      $key = $gsc->get_auth_cache_key();
      $cc  = $this->getContainer()->get('clipper_cache');
      if ($cc->is_enabled()) {
        $res = $cc->delete($key);
        if ($res) {
          $output->writeln("<info>Info: Cache token has been removed.</info>");
        }
        else {
          $output->writeln("<info>Info: There were no cache token.</info>");
        }
      }
    }

    $service      = $gsc->setupFeasibilitySheet();
    $token_active = $service->refresh_auth_token();
    if (!empty($token_active)) {
      $output->writeln("<info>Success: Access token is active.</info>");
    }
    else {
      $output->writeln("<error>Error: There seems to be an issue during the authentication.</error>");
    }

    $timestamp = (microtime(TRUE) - $timestamp);
    $timestamp = number_format($timestamp, 4, '.', ',');
    $output->writeln("<info>Exit: Console completed id {$timestamp}secs.</info>");
  }
}