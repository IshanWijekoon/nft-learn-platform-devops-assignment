# 🎓 NFT Learning Platform

A revolutionary Web3-enabled educational platform that combines traditional online learning with blockchain technology, allowing learners to earn NFT certificates upon course completion.

## 🌟 Overview

The NFT Learning Platform is a comprehensive educational ecosystem that bridges the gap between traditional e-learning and Web3 technology. Students can enroll in courses, track their progress, and receive verifiable NFT certificates as proof of completion, while creators can monetize their educational content through a secure, transparent platform.

## ✨ Key Features

### 🎯 For Learners
- **Browse & Enroll**: Discover courses across various categories and difficulty levels
- **Interactive Learning**: Watch video lessons with progress tracking
- **NFT Certificates**: Earn blockchain-verified certificates upon course completion
- **Profile Management**: Track learning journey and certificate collection
- **Search & Filter**: Find courses by category, difficulty, or instructor

### 👨‍🏫 For Creators
- **Course Creation**: Upload videos, set descriptions, and define learning outcomes
- **Content Management**: Edit and update course materials
- **Analytics Dashboard**: Track enrollment and completion rates
- **Revenue Generation**: Monetize educational content
- **Profile Customization**: Build instructor reputation and showcase expertise

### 🛡️ For Administrators
- **Course Approval**: Review and approve submitted courses
- **User Management**: Monitor platform activity and user statistics
- **Analytics**: Track platform growth and engagement metrics
- **Content Moderation**: Ensure quality and appropriate content
- **Certificate Management**: Oversee NFT certificate issuance

## 🏗️ System Architecture

### Frontend
- **HTML5/CSS3**: Responsive design with dark theme
- **JavaScript**: Interactive UI components and AJAX functionality
- **Bootstrap**: Mobile-first responsive framework

### Backend
- **PHP**: Server-side logic and API endpoints
- **MySQL**: Relational database for user data and course content
- **Session Management**: Secure user authentication and authorization

### Blockchain Integration
- **NFT Certificates**: Smart contract-based certificate generation
- **Verification System**: Blockchain-based credential verification
- **Wallet Integration**: Support for Web3 wallet connections

## 📱 User Roles & Permissions

| Role | Permissions |
|------|-------------|
| **Guest** | Browse courses, view course details |
| **Learner** | Enroll in courses, track progress, earn certificates |
| **Creator** | Create courses, manage content, view analytics |
| **Admin** | Full platform management, user oversight, content approval |

## 🚀 Getting Started

### Prerequisites
- **XAMPP** (Apache, MySQL, PHP)
- **Web Browser** (Chrome, Firefox, Safari, Edge)
- **Text Editor** (VS Code, Sublime, etc.)

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-username/nft-learning-platform.git
   cd nft-learning-platform
   ```

2. **Setup XAMPP**
   - Install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Start Apache and MySQL services
   - Place project files in `htdocs` directory

3. **Database Configuration**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `nft_learning_platform`
   - Import the provided SQL schema file
   - Update database credentials in `db.php`

4. **File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/course_videos/
   chmod 755 uploads/course_thumbnails/
   chmod 755 uploads/profile_pictures/
   chmod 755 uploads/nft_certificates/
   ```

5. **Environment Setup**
   - Configure file upload limits in `php.ini`
   - Set appropriate timezone settings
   - Enable required PHP extensions

### Database Schema

```sql
-- Core Tables
CREATE TABLE admins (id, full_name, email, password, created_at);
CREATE TABLE creators (id, full_name, email, password, bio, created_at);
CREATE TABLE learners (id, full_name, email, password, created_at);
CREATE TABLE courses (id, creator_id, course_name, description, category, difficulty, duration, status, nft_reward, created_at);
CREATE TABLE enrollments (id, learner_id, course_id, enrolled_at, completed, completed_at);
CREATE TABLE course_videos (id, course_id, video_title, video_path, video_order);
CREATE TABLE admin_actions (id, admin_id, action_type, target_id, details, created_at);
```

## 📁 Project Structure

```
nft-learning-platform/
├── 📁 assets/
│   └── 📁 css/
│       ├── auth.css          # Authentication styles
│       ├── courses.css       # Course display styles
│       ├── dark-theme.css    # Dark theme variables
│       ├── forms.css         # Form styling
│       ├── global.css        # Global styles
│       └── navigation.css    # Navigation components
├── 📁 uploads/
│   ├── 📁 course_thumbnails/ # Course preview images
│   ├── 📁 course_videos/     # Video content files
│   ├── 📁 creator_pictures/  # Creator profile images
│   ├── 📁 nft_certificates/  # Generated NFT certificates
│   └── 📁 profile_pictures/  # User profile images
├── 📄 admin.php             # Admin dashboard
├── 📄 db.php                # Database connection
├── 📄 login.html            # Login interface
├── 📄 register.html         # Registration interface
├── 📄 home-learner.php      # Learner dashboard
├── 📄 home-creator.php      # Creator dashboard
├── 📄 course-browser.php    # Course catalog
├── 📄 create_course.php     # Course creation
├── 📄 course-watching.php   # Video player
├── 📄 my_certificates.php   # Certificate gallery
└── 📄 README.md            # Project documentation
```

## 🎨 Features in Detail

### Course Management
- **Video Upload**: Support for multiple video formats
- **Thumbnail Generation**: Automatic or custom course previews
- **Progress Tracking**: Real-time completion monitoring
- **Interactive Elements**: Quizzes and assessments

### NFT Certificate System
- **Blockchain Verification**: Tamper-proof certificates
- **Custom Design**: Branded certificate templates
- **Metadata Storage**: Course and achievement details
- **Transfer Capability**: Shareable credential ownership

### User Experience
- **Responsive Design**: Mobile and desktop optimized
- **Dark Theme**: Eye-friendly interface
- **Search Functionality**: Advanced course discovery
- **Social Features**: Instructor profiles and reviews

## 🔧 Configuration

### Environment Variables
```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nft_learning_platform');

// File Upload Limits
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', 300);
```

### Security Settings
- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- File upload validation and sanitization
- Session security with secure cookies

## 🔐 Security Features

- **Authentication**: Secure login system with role-based access
- **Authorization**: Permission-based feature access
- **Input Validation**: Comprehensive data sanitization
- **File Security**: Upload restrictions and validation
- **Session Management**: Secure session handling

## 📊 Analytics & Reporting

### Admin Dashboard
- User registration trends
- Course completion rates
- Platform engagement metrics
- Revenue and growth analytics

### Creator Analytics
- Course performance statistics
- Student enrollment tracking
- Completion rate analysis
- Revenue reporting

## 🚧 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify MySQL service is running
   - Check database credentials in `db.php`
   - Ensure database exists and is accessible

2. **File Upload Issues**
   - Check PHP upload limits
   - Verify directory permissions
   - Ensure sufficient disk space

3. **Video Playback Problems**
   - Confirm video format compatibility
   - Check file path accuracy
   - Verify web server MIME types

## 🔮 Future Enhancements

- **Mobile App**: Native iOS and Android applications
- **Live Streaming**: Real-time interactive classes
- **AI Integration**: Personalized learning recommendations
- **Blockchain Expansion**: Multi-chain NFT support
- **Advanced Analytics**: Machine learning insights
- **Marketplace**: Creator content monetization platform

## 🤝 Contributing

We welcome contributions to improve the NFT Learning Platform! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📜 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **XAMPP Community** for the development environment
- **Bootstrap Team** for the responsive framework
- **PHP Community** for the robust backend language
- **Web3 Developers** for blockchain integration resources

## 📞 Support

For support and questions:
- 📧 **Email**: support@nftlearningplatform.com
- 💬 **Discord**: [Join our community](https://discord.gg/nftlearning)
- 📖 **Documentation**: [Full docs](https://docs.nftlearningplatform.com)
- 🐛 **Issues**: [GitHub Issues](https://github.com/your-username/nft-learning-platform/issues)

---

<div align="center">

**Built with ❤️ for the future of education**

[Website](https://nftlearningplatform.com) • [Documentation](https://docs.nftlearningplatform.com) • [Community](https://discord.gg/nftlearning)

</div>