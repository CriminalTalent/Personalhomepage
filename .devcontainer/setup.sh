
#!/bin/bash
echo "🚀 아보카도 에디션 개발환경 설정 중..."

# MySQL 시작 및 데이터베이스 생성
sudo service mysql start
mysql -u root -ppassword -e "CREATE DATABASE IF NOT EXISTS avocado_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -ppassword -e "CREATE USER IF NOT EXISTS 'avocado'@'localhost' IDENTIFIED BY 'password';"
mysql -u root -ppassword -e "GRANT ALL PRIVILEGES ON avocado_db.* TO 'avocado'@'localhost';"
mysql -u root -ppassword -e "FLUSH PRIVILEGES;"

# Apache 설정
sudo a2enmod rewrite
sudo a2enmod headers

# 가상호스트 설정
sudo tee /etc/apache2/sites-available/avocado.conf > /dev/null <<EOF
<VirtualHost *:80>
    DocumentRoot /workspaces/$(basename $PWD)/avocado-core
    
    <Directory /workspaces/$(basename $PWD)/avocado-core>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog /var/log/apache2/avocado_error.log
    CustomLog /var/log/apache2/avocado_access.log combined
</VirtualHost>
EOF

sudo a2dissite 000-default
sudo a2ensite avocado
sudo service apache2 restart

# 권한 설정
sudo chown -R www-data:www-data /workspaces/$(basename $PWD)
sudo chmod -R 755 /workspaces/$(basename $PWD)

# data 폴더 생성
mkdir -p data/{uploads,cache,logs}
chmod 707 data

# Node.js 도구 설치
npm install

echo "✅ 설정 완료!"
echo "📝 http://localhost 에서 아보카도 에디션을 설치하세요."
echo "🔧 MySQL: avocado/password @ localhost"
