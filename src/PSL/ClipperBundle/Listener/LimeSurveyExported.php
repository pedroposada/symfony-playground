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

class LimeSurveyExported extends FqProcess
{
  public function main(FirstQProjectEvent $event)
  {
    // Get FirstQGroup object
    $fqg = $event->getFirstQProjectGroup();

    // Resources
    $logger = $this->container->get('monolog.logger.clipper');
    $em = $this->container->get('doctrine')->getManager();
    $parameters_clipper = $this->container->getParameter('clipper');

    // If order was closed by other project iteration, throw info exception.
    if ($fqg->getState() === $parameters_clipper['state_codes']['order_closed']) {
      $message = "Order already closed.";
      throw new Exception($message, parent::LOGINFO);
    }

    // Get projects repo
    $fqps = $em->getRepository('PSLClipperBundle:FirstQProject');
    // Get count of projects
    $fqps_total = $fqps->createQueryBuilder('fqp')
      ->select('count(fqp.id)')
      ->where('fqp.firstqgroup = :fqgid')
      ->setParameter('fqgid', $fqg)
      ->getQuery()
      ->getSingleScalarResult();
    // Get count of exported projects
    $fqps_exported = $fqps->createQueryBuilder('fqp')
      ->select('count(fqp.id)')
      ->where('fqp.state = :state')
      ->andWhere('fqp.firstqgroup = :fqgid')
      ->setParameter('state', $parameters_clipper['state_codes']['limesurvey_exported'])
      ->setParameter('fqgid', $fqg)
      ->getQuery()
      ->getSingleScalarResult();

    $logger->info('Exported: ' . $fqps_exported . '. Total:' . $fqps_total);

    // Compare
    if ($fqps_exported === $fqps_total) {
      // Completed
      $logger->info('FQG ' . $fqg->getId() . '\'s projects are completely exported. Closing group.');
      $fqg->setState($parameters_clipper['state_codes']['order_closed']);
      $em->persist($fqg);
      $em->flush();

      // Send email.
      // Previously on LimeSurveyComplete. Moved here in CLIP-128.

      // Email to client when quota has been reached and the report is ready.
      // link to project report with quick-login of the user.
      if (empty($this->user['mail'])) {
        $message = "User has no email address for order id: [" . $fqg->getId() . "]";
        throw new Exception($message, parent::LOGERROR);
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

    } else {
      // Still not completed
      $message = 'FQG ' . $fqg->getId() . ' still needs ' . ($fqps_total - $fqps_exported) . ' exported projects to be closed.';
      throw new Exception($message, parent::LOGINFO);
    }
    
  }

}
