<?php

namespace PSL\ClipperBundle;

final class ClipperEvents
{
    /**
     * The event listeners receive an
     * PSL\ClipperBundle\Entity\FirstQProject instance.
     *
     * @var string
     */
    const FQ_PROCESS            = 'fq_process';
    
    // before
    const BEFORE_ORDER_COMPLETE        = 'before_order_complete';
    const BEFORE_LIMESURVEY_CREATED    = 'before_limesurvey_created';
    const BEFORE_RPANEL_COMPLETE       = 'before_rpanel_complete';
    const BEFORE_LIMESURVEY_COMPLETE   = 'before_limesurvey_complete';
    const BEFORE_EMAIL_SENT            = 'before_email_sent';
    
    // after
    const AFTER_ORDER_COMPLETE        = 'after_order_complete';
    const AFTER_LIMESURVEY_CREATED    = 'after_limesurvey_created';
    const AFTER_RPANEL_COMPLETE       = 'after_rpanel_complete';
    const AFTER_LIMESURVEY_COMPLETE   = 'after_limesurvey_complete';
    const AFTER_EMAIL_SENT            = 'after_email_sent';
}