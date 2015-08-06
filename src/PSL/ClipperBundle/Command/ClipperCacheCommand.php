<?php
/**
 * PSL/ClipperBundle/Command/ClipperCacheCommand.php
 *
 * Clipper Cache Command Class
 * This is the class that interactions with local db cache service
 *
 * @version 1.0
 * @date 2015-08-03
 */

namespace PSL\ClipperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClipperCacheCommand extends ContainerAwareCommand {

  private $actions = array('status', 'flush', 'get');
  private $service;
  private $logger;

  protected function configure() {
    $this->setName('clipper:cache')
         ->setDescription('ClipperCache')
         ->addArgument('action', InputArgument::REQUIRED, 'What kind of action do you want? ' . implode(', ', $this->actions))
         ->addArgument('extra', InputArgument::OPTIONAL, 'See Help', 'expired');
    $help = array(
      'Value of <extra> for following actions;',
      '- get   : the cache name, best use double quote if name contains space / symbols.',
      '          example: $ app/console clipper:cache get "gdoc-client-auth-service-token"',
      '',
      '- flush : record type "expired" or "all" records. Default "expired."',
      '          example: $ app/console clipper:flush expired',
      '',
    );
    $this->setHelp(implode("\n", $help));
  }

  protected function check_service_status() {
    if ($this->service->is_enabled()) {
      $timed = $this->service->get_cache_time();
      $this->logger->info("ClipperCache is active with default of {$timed} hour of lifetime.");
      return TRUE;
    }
    $this->logger->error("ClipperCache is disabled.");
    return FALSE;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $timestamp = microtime(TRUE);
    $this->service = $this->getContainer()->get('clipper_cache');
    $this->logger  = $this->getContainer()->get('monolog.logger.clipper');

    $action  = $input->getArgument('action');
    $extra   = $input->getArgument('extra');
    $verbose = $input->getOption('verbose');
    $status  = $this->check_service_status();
    if (!$status) {
      return;
    }
    switch ($action) {
      case 'status':
        $all      = $this->service->get_cache_count(FALSE);
        $active   = $this->service->get_cache_count(TRUE);
        $inactive = ($all - $active);
        $all      = number_format($all, 0, ',', '');
        $active   = number_format($active, 0, ',', '');
        $inactive = number_format($inactive, 0, ',', '');
        if (empty($verbose)) {
          $output->writeln("<info>Info: Found {$all} records:</info>");
          $output->writeln(" - Active  : {$active}");
          $output->writeln(" - Expired : {$inactive}");
        }
        else {
          $this->logger->info("Found {$all} records:", array('active' => $active, 'inactive' => $inactive));
        }
        break;

      case 'flush':
        $msg = ($extra == 'all' ? 'all' : 'expired');
        $this->logger->info("Flushing {$msg} cache records");
        $done = $this->service->flush(($extra == 'all'));
        if ($done !== FALSE) {
          $this->logger->info("Flush complete", array('extra' => $extra, 'removed' => $done));
          break;
        }
        $this->logger->error("Flush failed", array('extra' => $extra));
        break;

      case 'get':
        if (empty($extra)) {
          $this->logger->error("Please define Cache name.");
          break;
        }
        $value = $this->service->get($extra, TRUE, FALSE);
        if ($value === FALSE) {
          $this->logger->error("Requested Cache named '{$extra}' was not available.");
          break;
        }
        $value['has_expired'] = ($value['has_expired'] ? 'Yes' : 'No');
        if (empty($verbose)) {
          $output->writeln("<info>Info: Cached name found:</info>");
          $type = gettype($value['data']);
          $value['data'] = print_r($value['data'], TRUE);
          if ($type == 'string') {
            if (is_numeric($value['data'])) {
              //handle int / double
              $value['data'] = (($value['data'] == (int) $value['data']) ? (int) $value['data'] : (float) $value['data']);
              $type = gettype($value['data']);
            }
            elseif (($bool = (array_search((strtolower($value['data'])), array('false', 'true')))) !== FALSE) {
              //handle bool
              $value['data'] = (bool) $bool;
              $type = gettype($value['data']);
            }
            else {
              //handle other string
              $data_test_json = json_decode($value['data']);
              if ((!is_null($data_test_json)) && ($data_test_json !== FALSE)) {
                $type = 'JSON';
                $value['data'] = print_r($data_test_json, TRUE);
              }
            }
          }
          $set = array(
            'Name'           => $value['name'],
            'Expired on'     => $value['expiries'],
            'Has Expired'    => $value['has_expired'],
            "Data ({$type})" => $value['data'],
          );
          $mlenght = 0;
          array_map(function($key) use (&$mlenght) {
            $mlenght = max($mlenght, strlen($key));
          }, array_keys($set));
          foreach ($set as $key => $value) {
            $msg = array(
              ' - ',
              str_pad($key, $mlenght, ' '),
              ' : ',
              $value
            );
            $output->writeln(implode('', $msg));
          }
        }
        else {
          $this->logger->info("Cached name found:", $value);
        }
        break;

      default:
        $this->logger->error("Unknown action.", array('available-actions' => $this->actions, 'requested' => $action));
        break;
    };
    $timestamp = (microtime(TRUE) - $timestamp);
    $timestamp = number_format($timestamp, 4, '.', ',');
    $this->logger->info('Console completed', array('timed-secs', $timestamp));
  }
}