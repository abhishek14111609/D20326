<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Duos API Documentation",
 *     description="API documentation for Duos application - A social matching platform",
 *     @OA\Contact(
 *         email="info@duos.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Duos API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     required={"name", "email", "mobile"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="mobile", type="string", example="+1234567890"),
 *     @OA\Property(property="gender", type="string", example="male"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
 *     @OA\Property(property="bio", type="string", example="Hello, I'm John!"),
 *     @OA\Property(property="interests", type="array", @OA\Items(type="string"), example={"Travel", "Music"}),
 *     @OA\Property(property="profile_photo", type="string", format="url", example="https://example.com/profile.jpg"),
 *     @OA\Property(property="is_verified", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="LoginCredentials",
 *     required={"mobile", "password"},
 *     @OA\Property(
 *         property="mobile", 
 *         type="string", 
 *         example="+1234567890",
 *         description="User's mobile number in E.164 format"
 *     ),
 *     @OA\Property(
 *         property="password", 
 *         type="string", 
 *         format="password", 
 *         example="password123",
 *         description="User's password"
 *     ),
 *     @OA\Property(
 *         property="device_type", 
 *         type="string", 
 *         enum={"android", "ios", "web"}, 
 *         example="android",
 *         description="Type of device used for login"
 *     ),
 *     @OA\Property(
 *         property="device_token", 
 *         type="string", 
 *         example="device_push_token_123",
 *         description="Device token for push notifications"
 *     ),
 *     @OA\Property(
 *         property="remember_me", 
 *         type="boolean", 
 *         example=true,
 *         description="Whether to extend the token expiration time"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="RegisterData",
 *     required={"name", "email", "password", "password_confirmation", "mobile", "gender", "registration_type"},
 *     @OA\Property(
 *         property="name", 
 *         type="string", 
 *         example="John Doe",
 *         maxLength=255,
 *         description="User's full name"
 *     ),
 *     @OA\Property(
 *         property="email", 
 *         type="string", 
 *         format="email", 
 *         example="user@example.com",
 *         maxLength=255,
 *         description="User's email address (must be unique)"
 *     ),
 *     @OA\Property(
 *         property="password", 
 *         type="string", 
 *         format="password", 
 *         example="password123",
 *         minLength=8,
 *         description="Account password (min 8 characters)"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation", 
 *         type="string", 
 *         format="password", 
 *         example="password123",
 *         description="Password confirmation (must match password)"
 *     ),
 *     @OA\Property(
 *         property="mobile", 
 *         type="string", 
 *         example="+1234567890",
 *         pattern="^\\+[1-9]\\d{1,14}$",
 *         description="User's mobile number in E.164 format (must be unique)"
 *     ),
 *     @OA\Property(
 *         property="gender", 
 *         type="string", 
 *         enum={"male", "female", "other"}, 
 *         example="male",
 *         description="User's gender"
 *     ),
 *     @OA\Property(
 *         property="dob", 
 *         type="string", 
 *         format="date", 
 *         example="1990-01-01",
 *         description="User's date of birth (YYYY-MM-DD)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="bio", 
 *         type="string", 
 *         example="Hello, I'm John! I love hiking and photography.",
 *         maxLength=500,
 *         description="Short bio or description about the user",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="location", 
 *         type="string", 
 *         example="New York, USA",
 *         maxLength=255,
 *         description="User's current location",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="interest", 
 *         type="string", 
 *         example="Hiking, Photography, Travel",
 *         maxLength=255,
 *         description="User's interests or hobbies",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="hobby", 
 *         type="string", 
 *         example="Photography, Travel",
 *         maxLength=255,
 *         description="User's hobbies",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="registration_type", 
 *         type="string", 
 *         enum={"single", "duo"}, 
 *         example="single",
 *         description="Type of registration - 'single' for individual, 'duo' for couple"
 *     ),
 *     @OA\Property(
 *         property="social_provider", 
 *         type="string", 
 *         enum={"google", "facebook", "apple"}, 
 *         example="google",
 *         description="Social login provider (if registering via social login)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="social_id", 
 *         type="string", 
 *         example="1234567890",
 *         description="Social provider's user ID (if registering via social login)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="access_token", 
 *         type="string", 
 *         example="ya29.a0ARrdaM...",
 *         description="Access token from social provider (if registering via social login)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="identity_token", 
 *         type="string", 
 *         example="eyJhbGciOiJSUzI1NiIs...",
 *         description="Identity token from social provider (if registering via Apple or other providers that use it)",
 *         nullable=true
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="OTPVerification",
 *     required={"mobile", "otp"},
 *     @OA\Property(property="mobile", type="string", example="+1234567890"),
 *     @OA\Property(property="otp", type="string", example="123456")
 * )
 *
 * @OA\Schema(
 *     schema="DuoRegisterData",
 *     required={"email", "password", "password_confirmation", "mobile", "gender", "couple_name", "partner1_name", "partner1_email", "partner2_name", "partner2_email"},
 *     @OA\Property(
 *         property="email", 
 *         type="string", 
 *         format="email", 
 *         example="couple@example.com",
 *         maxLength=255,
 *         description="Primary email address for the couple's account (must be unique)"
 *     ),
 *     @OA\Property(
 *         property="password", 
 *         type="string", 
 *         format="password", 
 *         example="password123",
 *         minLength=8,
 *         description="Account password (min 8 characters)"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation", 
 *         type="string", 
 *         format="password", 
 *         example="password123",
 *         description="Password confirmation (must match password)"
 *     ),
 *     @OA\Property(
 *         property="mobile", 
 *         type="string", 
 *         example="+1234567890",
 *         pattern="^\\+[1-9]\\d{1,14}$",
 *         description="Primary mobile number for the couple's account (must be unique)"
 *     ),
 *     @OA\Property(
 *         property="gender", 
 *         type="string", 
 *         enum={"male", "female", "other"}, 
 *         example="other",
 *         description="Primary account holder's gender"
 *     ),
 *     @OA\Property(
 *         property="dob", 
 *         type="string", 
 *         format="date", 
 *         example="1990-01-01",
 *         description="Primary account holder's date of birth (YYYY-MM-DD)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="bio", 
 *         type="string", 
 *         example="We are a fun-loving couple who enjoy traveling and trying new things together!",
 *         maxLength=500,
 *         description="Short bio or description about the couple",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="location", 
 *         type="string", 
 *         example="New York, USA",
 *         maxLength=255,
 *         description="Couple's current location",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="interest", 
 *         type="string", 
 *         example="Travel, Food, Hiking, Photography",
 *         maxLength=255,
 *         description="Couple's shared interests or hobbies",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="hobby", 
 *         type="string", 
 *         example="Cooking, Hiking, Photography",
 *         maxLength=255,
 *         description="Couple's shared hobbies",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="couple_name", 
 *         type="string", 
 *         example="John & Jane",
 *         maxLength=255,
 *         description="Display name for the couple"
 *     ),
 *     @OA\Property(
 *         property="partner1_name", 
 *         type="string", 
 *         example="John Smith",
 *         maxLength=255,
 *         description="Full name of the first partner"
 *     ),
 *     @OA\Property(
 *         property="partner1_email", 
 *         type="string", 
 *         format="email", 
 *         example="john.smith@example.com",
 *         maxLength=255,
 *         description="Email of the first partner (must be different from primary email)"
 *     ),
 *     @OA\Property(
 *         property="partner1_photo", 
 *         type="string", 
 *         format="binary",
 *         description="Profile photo for the first partner (JPEG, PNG, JPG, GIF, max 2MB)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="partner2_name", 
 *         type="string", 
 *         example="Jane Smith",
 *         maxLength=255,
 *         description="Full name of the second partner"
 *     ),
 *     @OA\Property(
 *         property="partner2_email", 
 *         type="string", 
 *         format="email", 
 *         example="jane.smith@example.com",
 *         maxLength=255,
 *         description="Email of the second partner (must be different from primary and first partner's email)"
 *     ),
 *     @OA\Property(
 *         property="partner2_photo", 
 *         type="string", 
 *         format="binary",
 *         description="Profile photo for the second partner (JPEG, PNG, JPG, GIF, max 2MB)",
 *         nullable=true
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="status", type="string", example="error"),
 *     @OA\Property(property="message", type="string", example="Error message here")
 * )
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     @OA\Property(property="status", type="string", example="success"),
 *     @OA\Property(property="message", type="string", example="Operation completed successfully")
 * )
 *
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     @OA\Property(property="first", type="string", format="url", example="http://example.com/api/resource?page=1"),
 *     @OA\Property(property="last", type="string", format="url", example="http://example.com/api/resource?page=5"),
 *     @OA\Property(property="prev", type="string", format="url", nullable=true, example="http://example.com/api/resource?page=1"),
 *     @OA\Property(property="next", type="string", format="url", nullable=true, example="http://example.com/api/resource?page=3")
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     @OA\Property(property="current_page", type="integer", example=2),
 *     @OA\Property(property="from", type="integer", example=11),
 *     @OA\Property(property="last_page", type="integer", example=5),
 *     @OA\Property(property="path", type="string", format="url", example="http://example.com/api/resource"),
 *     @OA\Property(property="per_page", type="integer", example=10),
 *     @OA\Property(property="to", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=50)
 * )
 *
 * @OA\Schema(
 *     schema="Setting",
 *     required={"key", "value", "type", "group", "display_name"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="key", type="string", example="app.name"),
 *     @OA\Property(property="value", type="string", example="My Application"),
 *     @OA\Property(property="type", type="string", enum={"string","text","number","boolean","url","timezone","json","array","select","radio","checkbox"}, example="string"),
 *     @OA\Property(property="group", type="string", example="general"),
 *     @OA\Property(property="display_name", type="string", example="Application Name"),
 *     @OA\Property(property="description", type="string", nullable=true, example="The name of the application"),
 *     @OA\Property(property="is_public", type="boolean", example=true),
 *     @OA\Property(property="options", type="object", nullable=true, 
 *         @OA\Property(property="option1", type="string", example="Value 1"),
 *         @OA\Property(property="option2", type="string", example="Value 2")
 *     ),
 *     @OA\Property(property="sort_order", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User registration and authentication"
 * )
 *
 * @OA\Tag(
 *     name="Social Auth",
 *     description="Social media authentication"
 * )
 *
 * @OA\Tag(
 *     name="User",
 *     description="User profile and account management"
 * )
 *
 * @OA\Tag(
 *     name="Settings",
 *     description="Application settings and configurations"
 * )
 *
 * @OA\Schema(
 *     schema="Conversation",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="other_user_id", type="integer", example=2),
 *     @OA\Property(property="last_message", type="string", example="Hello, how are you?"),
 *     @OA\Property(property="unread_count", type="integer", example=2),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="other_user",
 *         ref="#/components/schemas/User"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Message",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="conversation_id", type="integer", example=1),
 *     @OA\Property(property="sender_id", type="integer", example=1),
 *     @OA\Property(property="receiver_id", type="integer", example=2),
 *     @OA\Property(property="message", type="string", example="Hello, how are you?"),
 *     @OA\Property(property="message_type", type="string", enum={"text", "image", "video", "audio"}, example="text"),
 *     @OA\Property(property="media_url", type="string", nullable=true, example="https://example.com/media/image.jpg"),
 *     @OA\Property(property="is_read", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="sender",
 *         ref="#/components/schemas/User"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     @OA\Property(property="status", type="string", example="error"),
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="field_name",
 *             type="array",
 *             @OA\Items(type="string", example="The field name field is required.")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Gift",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Rose"),
 *     @OA\Property(property="description", type="string", example="A beautiful red rose"),
 *     @OA\Property(property="price", type="number", format="float", example=5.99),
 *     @OA\Property(property="image_url", type="string", format="url", example="https://example.com/images/rose.png"),
 *     @OA\Property(property="is_premium", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="UserGift",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="gift_id", type="integer", example=1),
 *     @OA\Property(property="sender_id", type="integer", example=1),
 *     @OA\Property(property="receiver_id", type="integer", example=2),
 *     @OA\Property(property="message", type="string", example="Here's a gift for you!"),
 *     @OA\Property(property="is_anonymous", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="gift",
 *         ref="#/components/schemas/Gift"
 *     ),
 *     @OA\Property(
 *         property="sender",
 *         ref="#/components/schemas/User"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="LeaderboardItem",
 *     @OA\Property(property="position", type="integer", example=1),
 *     @OA\Property(property="score", type="number", format="float", example=1500.75),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="change", type="integer", nullable=true, example=2)
 * )
 *
 * @OA\Schema(
 *     schema="LeaderboardCollection",
 *     type="object",
 *     @OA\Property(property="status", type="string", example="success"),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/LeaderboardItem")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="type", type="string", example="weekly"),
 *         @OA\Property(property="updated_at", type="string", format="date-time")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="LeaderboardPosition",
 *     type="object",
 *     @OA\Property(property="status", type="string", example="success"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="position", type="integer", example=5),
 *         @OA\Property(property="score", type="number", format="float", example=1250.5),
 *         @OA\Property(property="user", ref="#/components/schemas/User"),
 *         @OA\Property(property="top_percentage", type="number", format="float", example=95.5)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="mobile", type="string", example="+1234567890"),
 *     @OA\Property(property="gender", type="string", example="male"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
 *     @OA\Property(property="bio", type="string", example="Hello, I'm John!"),
 *     @OA\Property(property="interests", type="array", @OA\Items(type="string"), example={"Travel", "Music"}),
 *     @OA\Property(property="profile_photo", type="string", format="url", example="https://example.com/profile.jpg"),
 *     @OA\Property(property="is_verified", type="boolean", example=true),
 *     @OA\Property(property="is_online", type="boolean", example=true),
 *     @OA\Property(property="last_seen", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="LeaderboardEntry",
 *     type="object",
 *     @OA\Property(property="position", type="integer", example=10),
 *     @OA\Property(property="score", type="number", format="float", example=1200.5),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="user_name", type="string", example="John Doe"),
 *     @OA\Property(property="user_photo", type="string", format="url", example="https://example.com/profile.jpg"),
 *     @OA\Property(property="is_current_user", type="boolean", example=true),
 *     @OA\Property(property="rank_change", type="integer", nullable=true, example=2)
 * )
 *
 * @OA\Schema(
 *     schema="ReportResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="user"),
 *     @OA\Property(property="reason", type="string", example="Inappropriate behavior"),
 *     @OA\Property(property="reported_type", type="string", nullable=true, example="user"),
 *     @OA\Property(property="reported_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="evidence", type="array", 
 *         @OA\Items(type="string", format="url", example="https://example.com/evidence.jpg")
 *     ),
 *     @OA\Property(property="additional_info", type="object", nullable=true),
 *     @OA\Property(property="admin_notes", type="string", nullable=true, example="Needs review"),
 *     @OA\Property(property="action_taken", type="string", nullable=true, example="User warned"),
 *     @OA\Property(property="reported_user", ref="#/components/schemas/User"),
 *     @OA\Property(property="reporter", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ApiDocumentationController extends Controller
{
    // This is just a placeholder for Swagger annotations
}
