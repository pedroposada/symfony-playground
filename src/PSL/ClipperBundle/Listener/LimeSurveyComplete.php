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
  public function main(FirstQProjectEvent $event)
  {
    // Get FirstQProject object
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
      if (empty($this->user['mail'])) {
        throw new Exception("user has no email address for order id: [$fqg->getId()]", 2);
      }
      $fwsso_quicklogin_user = new FWSSOQuickLoginUser('', '', $this->user['mail'], '', array());
      $hash = $fwsso_quicklogin_user->getQuickLoginHash($this->container->getParameter('clipper.users.ql_encryptionkey'));

      // http://localhost:9000/#quick-login&op=select-project&order_id=4db9db84-589f-11e5-bff2-4ffbfe082dc5&ql_hash=GhbXpDS2kh4gRI6QcQmF9nui5UNTydBb_c1_j4STRR2FR8bL58DidYiKOcF9y4YdD1R3qmnI1PXXMEkiH2KAlA,,
      $link = $this->container->getParameter('clipper.frontend.url')
            . '#quick-login'
            . '&op=select-project'
            . '&order_id=' . $fqg->getId()
            . '&ql_hash=' . $hash;

      $message = \Swift_Message::newInstance()
        ->setContentType('text/html')
        ->setSubject('Quota has been reached')
        ->setFrom(array($this->container->getParameter('clipper.no_reply_email')))
        ->setTo(array($this->user['mail']))
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
