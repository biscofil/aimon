# aimon
A simple PHP interface for the Aimon SMS service

Installation
------------

```bash
composer require biscofil/aimon
```

Usage
-----

```php
 $aimon = new AimonInterface("authlogin", "authpasswd");
 $aimon->sendSmsMessage("number", "message");
 $aimon->sendProSmsMessage("number", "message", "sender");
```

In case of error, an AimonException is thown

Documentation
-------------

http://sms.aimon.it/documentazione_api/Documentazione_BCP_API.pdf
