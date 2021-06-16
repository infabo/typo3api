[![Build Status](https://travis-ci.com/infabo/typo3api.svg?branch=master)](https://travis-ci.org/Nemo64/typo3api)
[![Latest Stable Version](https://poser.pugx.org/nemo64/typo3api/v/stable)](https://packagist.org/packages/nemo64/typo3api)
[![Total Downloads](https://poser.pugx.org/nemo64/typo3api/downloads)](https://packagist.org/packages/nemo64/typo3api)
[![Monthly Downloads](https://poser.pugx.org/nemo64/typo3api/d/monthly)](https://packagist.org/packages/nemo64/typo3api)
[![License](https://poser.pugx.org/nemo64/typo3api/license)](https://packagist.org/packages/nemo64/typo3api)

# apis for easier typo3 handling

This extension abstracts some of the array configuration necessary to get things done in typo3. This will result in faster, easier and less annoying workflows.

# how to install

Use `composer require nemo64/typo3api` to install this extension. For anyone not using composer: make your project use composer first. Seriously, this extension is to ease your workflow but if you are still using the none-composer mode you have bigger workflow problems.

# how to use

Replace your TCA array with the `Typo3Api\Builder\TableBuilder`.

## TableBuilder

Create the TCA file in your extension like `Configuration/TCA/tx_ext_person.php`.
Then, instead of returning the a TCA array, you can use the TableBuilder.

```PHP
\Typo3Api\Builder\TableBuilder::create('tx_ext_person')
    ->configure(new \Typo3Api\Tca\LanguageConfiguration())
    ->configure(new \Typo3Api\Tca\EnableColumnsConfiguration())
    ->configure(new \Typo3Api\Tca\SortingConfiguration())

    // configure cache clearing so you don't need to provide cache clear capabilities to your backend users
    ->configure(new \Typo3Api\Tca\CacheTagConfiguration('tx_ext_person_###UID###'))
    ->configure(new \Typo3Api\Tca\CacheTagConfiguration('tx_ext_person'))

    // the actual fields
    ->configure(new \Typo3Api\Tca\Field\InputField('first_name', ['required' => true, 'localize' => false]))
    ->configure(new \Typo3Api\Tca\Field\InputField('last_name', ['required' => true, 'localize' => false]))
    ->configure(new \Typo3Api\Tca\Field\DateField('birthday'))
    ->configure(new \Typo3Api\Tca\Field\EmailField('email'))
    ->configure(new \Typo3Api\Tca\Field\ImageField('image', ['cropVariants' => ['default' => ['1:1']]]))

    // easily allow multiple phone numbers
    ->configure(new \Typo3Api\Tca\Field\InlineRelationField('phone_numbers', [
        'foreign_table' => \Typo3Api\Builder\TableBuilder::create('tx_ext_person_phone')
            ->configure(new \Typo3Api\Tca\SortingConfiguration())
            ->configure(new \Typo3Api\Tca\Field\SelectField('type', ['values' => ['business', 'private', 'other']]))
            ->configure(new \Typo3Api\Tca\Field\PhoneField('value'))
    ]))

    // use or create complex configurations and reuse them across tables
    ->configure(new \Typo3Api\Tca\Util\Address('Address'))

    // create new tabs (aka --div--) on the fly
    ->configureInTab('Notice', new \Typo3Api\Tca\Field\TextareaField('notice'))
;
```

That is all. You can now start using the tx_ext_person table.

## ContentElement

To Create a content element, use the TableBuilder inside `Configuration/TCA/Override/tt_content.php`.

```PHP
\Typo3Api\Builder\TableBuilder::create('tt_content', 'carousel')
    ->configure(new \Typo3Api\Tca\ContentElementConfiguration())
    // add more fields as you like
;
```
Or with more options.
```PHP
\Typo3Api\Builder\TableBuilder::create('tt_content', 'quote')
    ->configure(new \Typo3Api\Tca\ContentElementConfiguration([
        'name' => 'Quote element',
        'description' => 'Tell what other peaple are saying',
        'icon' => 'content-quote',
        'headline' => 'hidden', // adds only the headline field
    ]))
;
```

## run the unit tests

run `vendor/bin/phpunit`
