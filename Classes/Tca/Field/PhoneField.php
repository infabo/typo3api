<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This is basically an input field with useful defaults for storing phone numbers.
 * I've seen phone numbers entered in an unusable way a lot so I created this definition.
 *
 * For output I recommend giggsey/libphonenumber-for-php for formatting.
 */
class PhoneField extends InputField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        // there are 2 common "i want to write invalid" pitfalls that i see often:
        // - trying to put multiple phone numbers into 1 field
        // - trying to write the country code like +49 (0) 40 so that the number is invalid if you strip the braces
        //
        // Multiple phone numbers are prevented by not allowing commas and though the short max size of the field.
        // "Optional" parts are prevented by not allowing braces at all.
        // I still want to allow some formatting so I allow the space, minus and dash character.
        $resolver->setDefaults([
            // it's not easy finding the correct max size for a phone number
            // https://stackoverflow.com/a/4729239/1973256
            // https://support.apple.com/kb/TA46568
            // but i tried a little and i think 24 should fit most numbers even with formatting
            'max' => 24,
            'is_in' => '-– +*#1234567890',
            'localize' => false,
        ]);
    }
}
