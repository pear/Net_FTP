<?php

// $Id$

/**
 * This class implements the Observer part of a Subject-Observer
 * design pattern. It listens to the events sent by a Net_FTP instance.
 * This module had many influences from the Log_observer code.
 *
 * @version    1.3
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @author     Tobias Schlitt <toby@php.net>
 * @author     Chuck Hagenbuch <chuck@horde.org>
 * @access     public
 * @category   Networking
 * @package    Net_FTP
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 *
 * @example    observer_upload.php          An example of Net_FTP_Observer implementation.
 */

class Net_FTP_Observer
{
    /**
     * Instance-specific unique identification number.
     *
     * @var        integer
     * @since      1.3
     * @access     private
     */
    var $_id;

    /**
     * Creates a new basic Net_FTP_Observer instance.
     *
     * @since      1.3
     * @access     public
     */
    function Net_FTP_Observer()
    {
        $this->_id = md5(microtime());
    }

    /**
     * Returns the listener's identifier
     *
     * @return     string
     * @since      1.3
     * @access     public
     */
    function getId()
    {
        return $this->_id;
    }

    /**
     * This is a stub method to make sure that Net_FTP_Observer classes do
     * something when they are notified of a message.  The default behavior
     * is to just do nothing.
     * You should override this method.
     *
     * @param      mixed     $event         A hash describing the net event.
     *
     * @since      1.3
     * @access     public
     */
    function notify($event)
    {
        return;
    }
}
?>
