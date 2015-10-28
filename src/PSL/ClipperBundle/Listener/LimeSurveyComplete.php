<?php

namespace PSL\ClipperBundle\Listener;

use \Exception as Exception;
use \stdClass as stdClass;
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


    // Email to client when quota has been reached and the report is ready.
    // link to project report with quick-login of the user
    $user = $this->container->get('security.context')->getToken()->getUser();
    $fwsso_quicklogin_user = new FWSSOQuickLoginUser('', $user->getEmail(), '', array());
    $hash = $fwsso_quicklogin_user->getQuickLoginHash($this->container->getParameter('clipper.users.ql_encryptionkey'));
    $link = $this->container->getParameter('clipper.frontend.url') . '?ql_hash=' . $hash . '&order_id=' . $fqg->getId() . '#project';

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
