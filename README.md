
# Social Media backend API

This project built by Laravel and TDD approach.

This project is not yet complete but its features are:


Auth    : **`login logout register forgot-password`**

Profile : **`follow|unfollow|send-follow-request|accept-follow-request|reject-follow-request|update name,bio,username,profile_image,privacy`**  

User    : get user **`followers|followings|posts|profileImage|info`**  

Post    : **`create|read|update|delete|get comments|add comment|like|unlike`**  

Comment : **`delete|like|unlike`**


## Code Style

This project uses **php actions** and **laravel DI system** to decrease the complexity of controllers and also add flexibility to them.

These actions that located in **App\Actions** , are written with TDD approach that we can find those tests in **Tests\Feature\Actions** directory.

Built reusable and reliable components with those actions and categorized them.

I tried to consider solid principle in this project.
## API Reference
* require parameters have *

### Auth

| URI                   	| Method 	| Authenticated 	| Parameters                                                              	|
|-----------------------	|--------	|---------------	|-------------------------------------------------------------------------	|
| `api/login`           	| `POST` 	| No            	| `*email *password`                                                    	|
| `api/logout`          	| `POST` 	| Yes           	| -                                                                     	|
| `api/register`        	| `POST` 	| No            	| `*name *username *email *password *password_confirmation` `bio`       	|
| `api/forgot-password` 	| `POST` 	| No            	| `*email`                                                                	|


### Profile 

| URI                                   	| Method 	| Authenticated 	| Paramenters                             	|
|---------------------------------------	|--------	|---------------	|-----------------------------------------	|
| `api/follow/{user_id}`                	| `POST` 	| Yes           	| -                                       	|
| `api/unfollow/{user_id}`              	| `POST` 	| Yes           	| -                                       	|
| `api/accept-follow-request/{user_id}` 	| `POST` 	| Yes           	| -                                       	|
| `api/reject-follow-request/{user_id}` 	| `POST` 	| Yes           	| -                                       	|
| `api/update/name`                     	| `POST` 	| Yes           	| `*name`                                 	|
| `api/update/bio`                      	| `POST` 	| Yes           	| `*bio`                                  	|
| `api/update/username`                 	| `POST` 	| Yes           	| `*username`                             	|
| `api/update/profile-image`            	| `POST` 	| Yes           	| `*profile_image` => `file:jpeg,jpg,png` 	|
| `api/update/privacy`                      | `POST`    | Yes               |`*privacy` => `['private' , 'public']`     |
| `api/is-username-available`           	| `POST` 	| Yes           	| `*username`                             	|


###  User 

| URI                           	| Method 	| Authenticated 	| Parameters 	|
|-------------------------------	|--------	|---------------	|------------	|
| `api/{username}/followers`    	| `GET`  	| Yes           	| -          	|
| `api/{username}/followings`   	| `GET`  	| Yes           	| -          	|
| `api/{username}/posts`        	| `GET`  	| Yes           	| -          	|
| `api/{username}/profileImage` 	| `GET`  	| Yes           	| -          	|
| `api/{username}/info`         	| `GET`  	| Yes           	| -          	|


### Post 

| URI                            	| Method       	| Authenticated 	| Paramenters          	| Description                                       	|
|--------------------------------	|--------------	|---------------	|----------------------	|---------------------------------------------------	|
| `api/posts`                    	| `POST`       	| Yes           	| `*caption *medias` 	| store a post `medias : jpeg , jpg`                	|
| `api/posts`                    	| `GET`        	| Yes           	| -                    	| retrieve posts for homepage **not implemented**   	|
| `api/posts/{post_id}`          	| `GET`        	| Yes           	| -                    	| retrieve `post_id` post                           	|
| `api/posts/{post_id}`          	| `PUT\|PATCH` 	| Yes           	| `*caption`           	| update `post_id` post                             	|
| `api/posts/{post_id}`          	| `DELETE`     	| Yes           	| -                    	| delete `post_id` post                             	|
| `api/posts/{post_id}/comment`  	| `POST`       	| Yes           	| `*text`              	| insert a comment to `post_id` post                	|
| `api/posts/{post_id}/comments` 	| `GET`        	| Yes           	| -                    	| retrieve all comments of `post_id` post           	|
| `api/posts/{post_id}/{number}` 	| `GET`        	| Yes           	| -                    	| retrieve media number `number` of `post_id` post  	|
| `api/posts/{post_id}/like`     	| `POST`       	| Yes           	| -                    	| like `post_id` post                               	|
| `api/posts/{post_id}/unlike`     	| `POST`       	| Yes           	| -                    	| unlike `post_id` post if you have liked that post 	|


### Comment

| URI                                	| Method   	| Authenticated 	| Paramenters 	| Description                                                              	|
|------------------------------------	|----------	|---------------	|-------------	|--------------------------------------------------------------------------	|
| `api/comments/{comment_id}`        	| `DELETE` 	| Yes           	| -           	| delete `comment_id` comment if you have written it or you are post owner 	|
| `api/comments/{comment_id}/like`   	| `POST`   	| Yes           	| -           	| like `comment_id` comment                                                	|
| `api/comments/{comment_id}/unlike` 	| `POST`   	| Yes           	| -           	| unlike `comment_id` comment if you have liked that comment               	|

## Running Tests

To run tests, run the following command

```bash
  vendor/bin/phpunit
```

