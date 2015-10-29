<?php

namespace PSL\ClipperBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;
use \DateTime;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use PSL\ClipperBundle\Listener\FqProcess;
use PSL\ClipperBundle\Event\FirstQProjectEvent;
use PSL\ClipperBundle\Security\User\FWSSOQuickLoginUser;

class LimeSurveyComplete extends FqProcess
{
  protected function main(FirstQProjectEvent $event)
  {
    // get FirstQProject object
    $fqp = $event->getFirstQProject();
    $fqg = $event->getFirstQProjectGroup();

    // CLIP-75. Project complete is either:
    // When quota is reached OR time has expired; whichever comes first.
    $markets = $fqg->getFormDataByField('markets');
    $specialties = $fqg->getFormDataByField('specialties');
    $estimated_quota = array_sum($this->container->get('quota_map')->lookupMultiple($markets, $specialties));

    $em = $this->container->get('doctrine')->getManager();
    $responses = $em->getRepository('PSLClipperBundle:LimeSurveyResponse')->findByFirstqgroup($fqg);

    $estimated_completion_date = current($fqg->getFormDataByField('completion_date'));

    $now = new \DateTime('now');
    $completion_date = new \DateTime($estimated_completion_date);

    $quota_is_reached = ($estimated_quota <= count($responses));
    $time_has_expired = ($completion_date <= $now);

    if ($quota_is_reached || $time_has_expired) {
      // Email to client when quota has been reached and the report is ready.
      // link to project report with quick-login of the user.
      $user = $this->container->get('security.context')->getToken()->getUser();
      $fwsso_quicklogin_user = new FWSSOQuickLoginUser('', $user->getEmail(), '', array());
      $hash = $fwsso_quicklogin_user->getQuickLoginHash($this->container->getParameter('clipper.users.ql_encryptionkey'));

      // http://external.dev.csb.pslgroup.com/remote/fwreports.html#order_id=B086E0BB-0BB5-4FD8-9CC5-6CEB3B28C0AC&ql_hash=XJhPyUGjKVenxX3s1SNvAJYsmcC3CtArKwenb3omrN0,
      $link = $this->container->getParameter('clipper.frontend.url')
            . '#order_id=' . $fqg->getId()
            . '&ql_hash=' . $hash;

      $message = \Swift_Message::newInstance()
        ->setContentType('text/html')
        ->setSubject('Quota has been reached')
        ->setFrom(array('noreply@clipper.com' => 'No reply'))
        ->setTo(array($user->getEmail()))
        ->setBody(
          $this->container->get('templating')->render(
            'PSLClipperBundle:Emails:confirmation_emails.order_close.html.twig',
            array(
              'link' => $link,
            ),
            'text/html'
          )
        )
      ;

      $this->container->get('mailer')->send($message);
    }
  }

}
