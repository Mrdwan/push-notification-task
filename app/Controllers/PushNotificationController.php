<?php


namespace App\Controllers;


use App\Models\PushNotification;
use App\Models\PushNotificationQueue;

class PushNotificationController extends Controller
{
    /**
     * @api {post} / Request to send
     *
     * @apiVersion 0.1.0
     * @apiName send
     * @apiDescription This method saves the push notification and put it to the queue.
     * @apiGroup Sending
     *
     * @apiBody {string="send"} action API method
     * @apiBody {string} title Title of push notification
     * @apiBody {string} message Message of push notification
     * @apiBody {int} country_id Country ID
     *
     * @apiParamExample {json} Request-Example:
    {"action":"send","title":"Hello","message":"World","country_id":4}
     *
     * @apiSuccessExample {json} Success:
    {"success":true,"result":{"notification_id":123}}
     *
     * @apiErrorExample {json} Failed:
    {"success":false,"result":null}
     */
    public function sendByCountryId(string $title, string $message, int $countryId): ?array
    {
        // TODO: use DTO object instead of an array
        $notificationId = PushNotification::create([
            'title' => $title,
            'message' => $message,
            'country_id' => $countryId
        ]);

        if (!$notificationId)
            return null;

        // dispatch the notification to the users
        PushNotification::dispatch($title, $message, $countryId, $notificationId);

        return [
            'notification_id' => $notificationId
        ];
    }

    /**
     * @api {post} / Get details
     *
     * @apiVersion 0.1.0
     * @apiName details
     * @apiDescription This method returns all details by notification ID.
     * @apiGroup Information
     *
     * @apiBody {string="details"} action API method
     * @apiBody {int} notification_id Notification ID
     *
     * @apiParamExample {json} Request-Example:
    {"action":"details","notification_id":123}
     *
     * @apiSuccessExample {json} Success:
    {"success":true,"result":{"id":123,"title":"Hello","message":"World","sent":90000,"failed":10000,"in_progress":100000,"in_queue":123456}}
     *
     * @apiErrorExample {json} Notification not found:
    {"success":false,"result":null}
     */
    public function details(int $notificationID): ?array
    {
        $stats = PushNotification::statistics($notificationID);

        if (!$stats)
            return null;

        return [
            'id' => $stats['id'],
            'title' => $stats['title'],
            'message' => $stats['message'],
            'sent' => $stats['sent'],
            'failed' => $stats['failed'],
            'in_progress' => $stats['pending_count'],
            'in_queue' => $stats['pending_count'],
        ];
    }

    /**
     * @api {post} / Sending by CRON
     *
     * @apiVersion 0.1.0
     * @apiName cron
     * @apiDescription This method sends the push notifications from queue.
     * @apiGroup Sending
     *
     * @apiBody {string="cron"} action API method
     *
     * @apiParamExample {json} Request-Example:
    {"action":"cron"}
     *
     * @apiSuccessExample {json} Success and sent:
    {"success":true,"result":[{"notification_id":123,"title":"Hello","message":"World","sent":50000,"failed":10000},{"notification_id":124,"title":"New","message":"World","sent":20000,"failed":20000}]}
     *
     * @apiSuccessExample {json} Success, no notifications in the queue:
    {"success":true,"result":[]}
     */
    public function cron()
    {
        $limit = 10000;
        $offset = 0;
        $stats = [];
        $last_id = null;

        do {
            $notifications = PushNotificationQueue::getInChunks($limit, $last_id);

            // stop if reached 100k devices (task limit)
            if ($offset >= config('PUSH_TO_N_DEVICES_BY_CRONE') ?: 100000) {
                break;
            }

            foreach ($notifications as $notification) {
                $data = json_decode($notification['content'], true);
                $is_sent = PushNotification::send($data['title'], $data['message'], $data['token']);

                // idd the notification stats if not found -> hashtable to reduce searching time
                if (!isset($stats[$notification['push_notification_id']])) {
                    $stats[$notification['push_notification_id']] = [
                        'notification_id' => $notification['push_notification_id'],
                        'title' => $data['title'],
                        'message' => $data['message'],
                        'sent' => 0,
                        'failed' => 0
                    ];
                }

                $last_id = $notification['id'];

                if ($is_sent) {
                    $stats[$notification['push_notification_id']]['sent']++;
                } else {
                    $stats[$notification['push_notification_id']]['failed']++;
                }
            }

            // Update offset for the next iteration
            $offset += $limit;

            // delete once to reduce db hits
            $queueJobsIds = array_column($notifications, 'id');

            if (count($queueJobsIds)) {
                PushNotificationQueue::bulkDelete($queueJobsIds);
            }

            // Continue processing until there are no more records
        } while (!empty($notifications));

        if (!$stats)
            return null;

        PushNotification::updateStats($stats);

        return array_values($stats);
    }
}