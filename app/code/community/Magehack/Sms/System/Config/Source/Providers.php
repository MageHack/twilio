<?php

class Magehack_Sms_System_Config_Source_Providers
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'mediaburst',
                'label' => 'Mediaburst',
            ),
            array(
                'value' => 'twilio',
                'label' => 'Twilio',
            ),
        );
    }
}
