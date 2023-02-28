# PUSH NOTIFICATIONS

The app sends push notifications to all users by country. The push notification contains a title and a message. It sends to the user's device by the device token. 
We have 3 "actions":
- ``send`` saves the push notification in the databse and put it in the queue. We send push notifications by **cron** action.
- ``cron`` sends the push notification(s) to 100K devices. 
- ``details`` displays detailed information about the push notification.

#### Keep in mind:
- The user can have multiple devices.
- The device token can be expired (expired = 1).
- The **cron** action runs automatically every minute. So it's 100k devices per 1 minute.
- Use app\models\PushNotificationsModel::send() to send a push notification.
- You can create migrations or send the SQL dump of database.
- You can use the postman collection for testing.

#### What we did:
- The base things of app already implemented. Requests, handlers and responses.
- Tables countries, users and devices have been created. Also, you can use seeders.
- Documentation: {project}/documentation/index.html
- Postman collection: {project}/postman_collection.json

#### Requirements:
**PHP 7.4+**, **PDO** and no frameworks/libraries/wrappers, only your code and SQL queries.
The app should not only work, but also **work fast**. Think about the **optimization**.

---

### Start
1. Clone the repo: 
```
  git clone https://github.com/sevaske/test-push-notifications
```
3. Install: 
```
  composer install
```
3. Create a database and configure **app/configs/Database.php**
4. Run migrations and fill your database:
```
  php vendor/bin/phinx migrate
  php vendor/bin/phinx seed:run -s CountriesSeeder -s UsersSeeder -s DevicesSeeder
```

### What you need to do:
1. Read the documentation to understand the task.
2. Create tables for storing data about push notifications and queuing.
3. Implement app\controllers\PushNotificationsController->sendByCountryId()
4. Implement app\controllers\PushNotificationsController->cron()
5. Implement app\controllers\PushNotificationsController->details()

--- 

### When you're done
Send a link of your repository to s.mnatcakanian@gasable.com when you're done. Also, let us know how many hours you spent on this task.
