<?php
/**
 * Mediaburst SMS Magento Integration
 *
 * Copyright © 2011 by Mediaburst Limited
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND ISC DISCLAIMS ALL WARRANTIES WITH REGARD
 * TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND
 * FITNESS. IN NO EVENT SHALL ISC BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT,
 * OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF
 * USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE
 * OF THIS SOFTWARE.
 *
 * @category  Mage
 * @package   Mediaburst_Sms
 * @license   http://opensource.org/licenses/isc-license.txt
 * @copyright Copyright © 2011 by Mediaburst Limited
 * @author    Lee Saferite <lee.saferite@lokeycoding.com>
 */
/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run("
    -- DROP TABLE IF EXISTS {$this->getTable('Mediaburst_Sms/Message')};
    CREATE TABLE {$this->getTable('Mediaburst_Sms/Message')} (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `store_id` SMALLINT(5) UNSIGNED NOT NULL,
        `to` VARCHAR(40) NOT NULL,
        `from` VARCHAR(40),
        `content` VARCHAR(180),
        `status` TINYINT(1) NOT NULL DEFAULT 0,
        `message_id` VARCHAR(255),
        `error_number` INT(10) UNSIGNED,
        `error_description` VARCHAR(255),
        PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Mediaburst SMS Messages';
");

$this->run("
    ALTER TABLE `{$this->getTable('Mediaburst_Sms/Message')}`
        ADD KEY `FK_MESSAGE_STORE` (`store_id`),
        ADD CONSTRAINT `FK_MESSAGE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->endSetup();