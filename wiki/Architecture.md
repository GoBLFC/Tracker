Tracker uses Laravel for its backend.
Since Laravel is an MVC (Model, View, Controller) framework, that structure is generally adhered to.
[PSR-4](https://www.php-fig.org/psr/psr-4/) autoloading is in use, so as long as the namespace and class filesystem structure is followed, files don't need to be manually included/required.

For the frontend, things are a little more complicated since a significant rewrite is underway.
The modern frontend uses [Vue](https://vuejs.org/) & TypeScript, and is connected to the backend with [Inertia](https://inertiajs.com/).
The legacy frontend just uses plain Laravel Blade templates and JavaScript.
At this point, only the admin pages are left on the legacy design.

## Frequently Used Directories

-   Routes: [routes](/routes)
-   Controllers: [app/Http/Controllers](/app/Http/Controllers)
-   Models: [app/Models](/app/Models)
-   Views: [resources/views](/resources/views)
-   Artisan commands: [app/Console/Commands](/app/Console/Commands)
-   Database migrations: [database/migrations](/database/migrations)
-   Configuration: [config](/config)
-   JavaScript/TypeScript assets: [resources/js](/resources/js)
-   Style assets (Sass/SCSS): [resources/sass](/resources/sass)
-   Image assets: [resources/img](/resources/img)

## Custom Artisan Commands

Use `php artisan help` or `sail artisan help` to view a list of all available commands, not just custom ones.  
Use `php artisan help <command name>` or `sail artisan help <command name>` to view detailed information for a specific command.

| Name                   | Description                                                                                                                                                                     |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| auth:set-role          | Sets the role for a user. If user isn't specified on the CLI, then you will be prompted to search for and select the appropriate user, as well as to select the role to assign. |
| auth:fetch-unknown     | Retrieves and populates information for any users with the `unknown` username (previously-created Users that information couldn't be retrieved from ConCat for at the time).    |
| tracker:notify-rewards | Sends notifications to users for rewards they are newly eligible to claim. Automatically called by the task scheduler every 5 minutes.                                          |
| tracker:stop-ongoing   | Stops all ongoing time entries for the active event. Automatically called by the task scheduler every day at the configured day boundary hour.                                  |
| telegram:set-commands  | Sends the list of commands to Telegram to display to users interacting with the bot.                                                                                            |
| telegram:set-webhook   | Sends the webhook URL to Telegram. Requires the application to be accessed via HTTPS.                                                                                           |
| telegram:poll          | Polls Telegram for updates (primarily for development use).                                                                                                                     |

## Database Models

All database models are using UUIDv7 for their primary key (`id` column).
Eloquent is being used heavily for nearly all database interactions.
Foreign key constraints are used in the database whenever possible to ensure referential integrity at every step of the process.

All models and their relationships are listed below, alongside a brief description of their purpose.

| Name        | Table         | Description                                                                                                                                                                                  |
| ----------- | ------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Activity    | activities    | Used for tracking events and changes to models for audit logging purposes. Belongs to a User via both subject and causer.                                                                    |
| AttendeeLog | attendee_logs | A log to enter users into. Used for tracking attendance to a panel or other type of event. Has many Users, with `type` (`attendee` or `gatekeeper`) on the pivot table. Belongs to an Event. |
| Department  | departments   | An organizational unit for staff/volunteers of a convention.                                                                                                                                 |
| Event       | events        | A single convention/other type of event that time is tracked for.                                                                                                                            |
| Kiosk       | kiosks        | A device that has been authorized to allow volunteers to enter time on. These devices keep a cookie with the session key to identify themselves.                                             |
| QuickCode   | quick_codes   | One-time-use sign-in codes for users. Expires 30 seconds after creation. Belongs to a User.                                                                                                  |
| Reward      | rewards       | Possible reward/goal for volunteers to thank them for their time once they reach a threshold. Belongs to an Event.                                                                           |
| RewardClaim | reward_claims | Rewards claimed by users. Belongs to a User and a Reward.                                                                                                                                    |
| Setting     | settings      | Application settings identified by a string and stored as JSON.                                                                                                                              |
| TimeBonus   | time_bonuses  | Time periods that grant bonus volunteer time credit while being worked within. Belongs to an Event and many Departments.                                                                     |
| TimeEntry   | time_entries  | Volunteer time clocked by users. Belongs to a User, a Department, and an Event.                                                                                                              |
| User        | users         | Any user of the application. Has a role (Banned, Attendee, Volunteer, Staff, Lead, Manager, Admin) that determines permissions.                                                              |

## Permissions

Permissions are implemented very simply at the moment.
Users have a single assigned Role value, which is just an enum (Banned, Attendee, Volunteer, Staff, Lead, Manager, Admin).

-   Volunteer is the default role for users. They have time tracking capabilities, but not much else.
-   Leads can authorize/deauthorize Kiosks.
-   Managers can authorize/deauthorize Kiosks, view and manage volunteers' time entries, manage attendee log attendees and gatekeepers, and create users with a badge ID.
-   Admins can do anything, but especially are responsible for general entity CRUD operations.
-   Attendees are just users created for the purpose of being an entry in an attendee log. They are automatically "promoted" to Volunteer if they ever log in.
-   Banned users are prevented from interacting with the application entirely beyond signing in.

## Time Tracking Details

Since the primary purpose of Tracker is to track the time that volunteers spend working shifts, a lot of care has been put in to how that time is kept.

### Time Entries

In order for a volunteer to enter time, they must visit an authorized kiosk at the beginning and end of their shift.
When they clock in, they select a department the shift will be for, and a TimeEntry is created in the database with the current time as its `start` field and a null `stop` field.
Any TimeEntry with a null `stop` field is considered to be an "ongoing" entry.
An ongoing TimeEntry's duration is the amount of time between its `start` and the current time.
Upon clocking out, the ongoing TimeEntry is updated to fill in the `stop` field with the current time.
A volunteer may only ever have a single ongoing TimeEntry.

### Time Bonuses

An admin can create multiple TimeBonuses for an Event that apply within a time period (between `start` and `stop`) to specific Departments.
Any TimeEntry that is assigned to a Department + Event with a TimeBonus and has its time range even partially within a TimeBonus period has the TimeBonus' multiplier applied to the amount of time that is within the bonus period.

Example scenario:

-   A TimeBonus exists for departments "Crowd Control", "Operations", and "Art Gallery" for event "BLFC 2023", between the period of 2023-10-31 18:00 and 2023-10-31 20:00, with a multiplier of 2.
-   A TimeEntry is entered for the "Operations" department and "BLFC 2023" event, starting at 2023-10-31 16:00 and ending at 2023-10-31 19:00

The TimeEntry's raw duration will be 3hrs, and its "earned time" (duration with bonuses) will be 4hrs.
This is because the TimeEntry contained 1hr of time within the bonus period, so that single hour is considered to be worth double time.

### Auto-Stopping Ongoing Time Entries

Each day at the configured day boundary hour (see [config/tracker.php], default 04:00), the application has a scheduled task to automatically terminate any ongoing time entries.
This is to catch the cases where a volunteer forgets to clock out after their shift, thus leaving the time entry running perpetually.
When a time entry is terminated this way, its stop time is updated to be either 1hr after the start time or the current time, whichever is sooner.
A notification is sent that they're forced to acknowledge on the web page the next time they log in.

## Attendee Logs

Attendee logs are an entity used to track attendees for a scheduled event such as a panel or meetup.
They can have any number of users entered into them by badge ID, and they don't even require the users entered to be valid volunteers or staff.
Any number of Gatekeepers can also be added to them, who will all be able to view and manage the attendees that are logged, regardless of their own role.

## Telegram Bot

### Account Linking

Users can link their Telegram account to the application by scanning a QR code on the web page or manually visiting the proper Telegram link generated on it.
The QR code simply directs them to the same aforementioned link, which should automatically open Telegram and send a `/start <setup key>` command to the bot.
Every user has a Telegram setup key that is unique to them and randomly generated upon creation.
When the bot receives the start command with their setup code, it stores the Telegram chat ID on their User and uses this for future communication.

### Notifications

The bot will automatically send notifications to users with linked accounts for the following scenarios:

-   Their earned time has reached a reward's hours threshold
-   They have claimed a reward with a staff member
-   Their time entry has been auto-stopped at the day boundary hour

### Commands

When a user sends a message of any kind to the Telegram bot, Telegram contacts the configured webhook (`php artisan telegram:set-webhook`) to notify the application of the message.
The message is checked for a valid command - if there is one, the command is processed.
All Telegram commands are in [app/Telegram/Commands](/app/Telegram/Commands).
Telegram needs to be informed of these commands with `php artisan telegram:set-commands` so that it may present a convenient list to users of the bot.

During development, `php artisan telegram:poll` can be used instead of the webhook, which will start a long-term polling process to pull updates from Telegram rather than it pushing to the application.
This allows the Telegram bot to be tested without needing the application to be externally accessible to the internet.
