# Anonymous Code of Conduct Hotline

This small app let's you run an anonymous hotline for Code of Conduct violation reporting.  Obviously, you can use your Twilio account or the app's database to unmask an anonymous user, so take care in who you have administer this.  If you would like an unrelated third party to run your hotline, feel free to contact me, john@nejsconf.com, and I can run it for you.

![SMS Reporting](https://dl.dropboxusercontent.com/u/21819015/simple-coc/IMG_4120.PNG)
![Web Interface](https://dl.dropboxusercontent.com/u/21819015/simple-coc/IMG_4121.PNG)
![Voice Reporting](https://dl.dropboxusercontent.com/u/21819015/simple-coc/IMG_4122.PNG)

## How It Works

### SMS

Text messages come into Twilio and are forwarded to your installation of the app.  The essentials are recorded, and the reporter is assigned an id and unique token.  The message is then forwarded to your phone through Twilio, with the id prepended in brackets, like so:


    +----------+           +--------+            +------------+    +-------+
    |          |  Hello.   |        |  Hello.    |            |    |       |
    | Reporter +----1----->+        +-----2------>            +----> MySQL |
    |          |           |        |            |            |    |       |
    +----------+           |        |            |            |    +-------+
                           | Twilio |            | Simple CoC |
    +-----+                |        |            |            |
    |     |    [1] Hello.  |        | [1] Hello. |            |
    | You <-------4--------+        <-----3------+            |
    |     |                |        |            |            |
    +-----+                |        |            |            |
                           +--------+            +------------+

You will also be given a link where you can post replies through a form.  Alternatively, you can reply via SMS, making sure to start the message with the id of the reporter you wish to reply to in brackets, like so:


    +-----+                +--------+            +------------+    +-------+
    |     |   [1] Hi.      |        |  [1] Hi.   |            |    |       |
    | You +------1-------->+        +-----2------>            +----> MySQL |
    |     |                |        |            |            |    |       |
    +-----+                |        |            |            |    +-------+
                           | Twilio |            | Simple CoC |
    +----------+           |        |            |            |
    |          |    Hi.    |        |    Hi.     |            |
    | Reporter <-----4-----+        <-----3------+            |
    |          |           |        |            |            |
    +----------+           |        |            |            |
                           +--------+            +------------+

The app will strip the id from the message and direct it to the correct reporter.

### Voice

Voice is a simpler setup. When a call comes in the app will use Twilio to attempt to dial your phone. The calling number will appear as the Code of Conduct hotline number, and you will be connected to the reporter when you answer.

## Setting  It Up

You'll need a Twilio account, PHP 5.4+, Composer and MySQL or a variant (MariaDB, Percona, Drizzle, etc.)

  1. Create your database, and load in `sql/001.sql`
  1. Copy `config.php.example` to `config.php` and fill with your information
  1. Make sure your `secret` in the `config.php` is random and reasonably long
  1. Point your webserver at the `app/` directory and make sure things are running
  1. Get a phone number from Twilio, and add it to the `numbers` table in MySQL
  1. In Twilio, assign the SMS Request URL to `http://<your domain>/twiml/sms.php?secret=<your secret>` and the Voice Request URL to  `http://<your domain>/twiml/voice.php?secret=<your secret>`
  1. Test it, then give out the number on your Code of Conduct page.

If you have multiple numbers, perhaps for multiple events or multiple volunteers, you can add them to the `numbers` table and the app will differentiate between them.

### The Secret

The secret in `config.php` is what helps keep your app safe.  Each reporter gets a unique random token to make their URL harder to guess, but the secret makes all the URL's hard to guess.  Think of it as a password.

Accordingly, you should try to make it long, I'd recommend 30 characters at least.  You can use [pwgen](http://linux.die.net/man/1/pwgen) for this if you'd like, that's what I did.  `pwgen -N 1 30` for example.

## About

This was written the evening before [NEJS Conf 2016](https://nejsconf.com/) so it's a bit rushed and messy.  It works though!
