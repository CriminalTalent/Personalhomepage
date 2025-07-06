#!/bin/bash
echo "🎨 CriminalTalent의 개인 홈페이지 개발환경 설정 중..."

# MySQL 설정
sudo service mysql start
mysql -u root -e "CREATE DATABASE IF NOT EXISTS personal_homepage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "CREATE USER IF NOT EXISTS 'homepage'@'localhost' IDENTIFIED BY 'criminaltalent2024';"
mysql -u root -e "GRANT ALL PRIVILEGES ON personal_homepage.* TO 'homepage'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# Apache 설정
sudo a2enmod rewrite
sudo a2enmod headers

# 가상호스트 설정 (아보카도 에디션용)
sudo tee /etc/apache2/sites-available/homepage.conf > /dev/null <<EOF
<VirtualHost *:80>
    DocumentRoot /workspaces/Personalhomepage/avocado-edition
    ServerName localhost
    
    <Directory /workspaces/Personalhomepage/avocado-edition>
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
    </Directory>
    
    ErrorLog /var/log/apache2/homepage_error.log
    CustomLog /var/log/apache2/homepage_access.log combined
</VirtualHost>
EOF

sudo a2dissite 000-default
sudo a2ensite homepage
sudo service apache2 restart

# 권한 설정
sudo chown -R www-data:www-data /workspaces/Personalhomepage
sudo chmod -R 755 /workspaces/Personalhomepage

# 필요한 폴더 생성
mkdir -p data/{uploads,cache,logs,backup}
mkdir -p my-skins/postype-style/{templates,assets/{css,js,images},preview}
mkdir -p skin-manager/{api,frontend,installer}

# 권한 설정 (아보카도 에디션 필요)
chmod 707 data

# Node.js 의존성 설치
if [ -f "package.json" ]; then
    npm install
fi

echo "✅ 설정 완료!"
echo ""
echo "📋 개발 정보:"
echo "🌐 웹사이트: http://localhost"
echo "🗄️  MySQL: homepage/criminaltalent2024 @ localhost"
echo "📂 데이터베이스: personal_homepage"
echo ""
echo "🎯 다음 단계:"
echo "1. avocado-edition/ 폴더에 아보카도 에디션 파일 업로드"
echo "2. http://localhost 접속하여 설치 진행"
echo "3. 스킨 개발 시작!"
