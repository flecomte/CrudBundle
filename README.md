FLECrudBundle
====================

[![Build Status](https://travis-ci.org/flecomte/CrudBundle.svg)](https://travis-ci.org/flecomte/CrudBundle)

[![Dependency Status](https://www.versioneye.com/user/projects/53d7891b3648f468870002ad/badge.svg)](https://www.versioneye.com/user/projects/53d7891b3648f468870002ad)

[![Coverage Status](https://coveralls.io/repos/flecomte/CrudBundle/badge.png)](https://coveralls.io/r/flecomte/CrudBundle)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/84a5360f-3171-4bd3-be6b-9f17009a74cd/small.png)](https://insight.sensiolabs.com/projects/84a5360f-3171-4bd3-be6b-9f17009a74cd)

Overview
--------


Installation
------------

Add the bundle to your `composer.json` file:
```json
require: {
    "jms/di-extra-bundle": "dev-master",
    "fle/crud-bundle": "1.*@dev"
}
```

Then run a composer update:

```bash
composer.phar update fle/crud-bundle
```

Register the bundle with your kernel in `AppKernel::registerBundles()`:
```php
<?php
$bundles = array(
    // ...
    new JMS\DiExtraBundle\JMSDiExtraBundle($this),
    new JMS\AopBundle\JMSAopBundle(),
    new FLE\Bundle\CrudBundle\FLECrudBundle(),
    // ...
);
```

Add Configuration in `app/config/config.yml`:
```yaml
jms_di_extra:
    locations:
        bundles:
            - FLECrudBundle

twig:
    form_themes:
        - 'FLECrudBundle::Form/fields.html.twig'
```


Usage
-----

```twig
{% extends 'FLECrudBundle::base.html.twig' %}
```