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

  protected function configure() {
    $this->setName('clipper:cache')
         ->setDescription('Clipper Cache')
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

  protected function setup_service() {

    $this->service = $this->getContainer()->get('clipper_cache');
  }

  protected function check_service_status($output) {
    if ($this->service->is_enabled()) {
      $timed = $this->service->get_cache_time();
      $output->writeln("<info>Info: Clipper Cache is active with default of {$timed} hour of lifetime.</info>");
      return TRUE;
    }
    $output->writeln("<error>Error: Clipper Cache is disabled.</error>");
    return FALSE;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setup_service();
    $action = $input->getArgument('action');
    $extra  = $input->getArgument('extra');
    $status = $this->check_service_status($output);
    if (!$status) {
      return;
    }
    switch ($action) {
      case 'status':
        $all      = $this->service->get_cache_count(FALSE);
        $active   = $this->service->get_cache_count(TRUE);
        $inactive = ($all - $active);
        $all = number_format($all, 0, ',', '');
        $output->writeln("<info>Info: Found {$all} records:</info>");
        $active = number_format($active, 0, ',', '');
        $output->writeln(" - Active  : {$active}");
        $inactive = number_format($inactive, 0, ',', '');
        $output->writeln(" - Expired : {$inactive}");
        break;

      case 'flush':
        $msg = ($extra == 'all' ? 'all' : 'expired');
        $output->writeln("<info>Info: Flushing {$msg} cache records.</info>");
        $done = $this->service->flush(($extra == 'all'));
        if ($done !== FALSE) {
          $output->writeln("<info>Info: Flush complete. {$done} records was removed.</info>");
          break;
        }
        $output->writeln("<error>Error: Flush failed.</error>");
        break;

      case 'get':
        if (empty($extra)) {
          $output->writeln("<error>Error: Please define Cache name, see --extra argument.</error>");
          break;
        }
        $value = $this->service->get($extra, TRUE, FALSE);
        if ($value === FALSE) {
          $output->writeln("<error>Error: Requested Cache named '{$extra}' was not available.</error>");
          break;
        }
        $output->writeln("<info>Info: Cached name found:</info>");
        $value['has_expired'] = ($value['has_expired'] ? 'Yes' : 'No');
        $type = gettype($value['data']);
        $value['data'] = print_r($value['data'], TRUE);
        if ($type == 'string') {
          $data_test_json = json_decode($value['data']);
          if ((!is_null($data_test_json)) && ($data_test_json !== FALSE)) {
            $type = 'JSON';
            $value['data'] = print_r($data_test_json, TRUE);
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
        break;

      default:
        $output->writeln("<error>Error: Unknown action \"{$action}\", choose " . implode(', ', $this->actions) . ".</error>");
        break;
    };
    $output->writeln("<info>Exit: Console complete.</info>");
  }
}