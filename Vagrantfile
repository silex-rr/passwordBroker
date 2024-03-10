# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://vagrantcloud.com/search.
  config.vm.box = "generic/ubuntu2204"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # NOTE: This will enable public access to the opened port
  config.vm.network "forwarded_port", guest: 8080, host: 80
  config.vm.network "forwarded_port", guest: 3306, host: 3360

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine and only allow access
  # via 127.0.0.1 to disable public access
  # config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.33.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "../data", "/vagrant_data"
  config.vm.synced_folder "./", "/app"

  # Disable the default share of the current code directory. Doing this
  # provides improved isolation between the vagrant box and your host
  # by making sure your Vagrantfile isn't accessible to the vagrant box.
  # If you use this you may want to enable additional shared subfolders as
  # shown above.
  # config.vm.synced_folder ".", "/vagrant", disabled: true

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Enable provisioning with a shell script. Additional provisioners such as
  # Ansible, Chef, Docker, Puppet and Salt are also available. Please see the
  # documentation for more information about their specific syntax and use.
  # config.vm.provision "shell", inline: <<-SHELL
  #   apt-get update
  #   apt-get install -y apache2
  # SHELL

  config.vm.provision "shell", inline: <<-SHELL
    sudo apt-get update
    sudo add-apt-repository ppa:ondrej/php
    sudo apt-get install -y php8.3 php8.3-fpm php8.3-mysql php8.3-zip php8.3-xml php8.3-sqlite3 php8.3-mbstring php8.3-curl php8.3-cli php8.3-bz2
    sudo cp /app/docker/php/php.ini /etc/php/8.3/fpm/php.ini
    sudo service php8.3-fpm restart
    sudo apt-get install -y composer
    sudo apt-get install -y mysql-server-8.0
    sudo apt-get install -y nginx
    sudo apt-get install -y mc
#     sudo echo "127.0.0.1 php_fpm" >> /etc/hosts
#     sudo mkdir -p /opt/bitnami/nginx/logs/
    sudo rm /etc/nginx/sites-enabled/default
    sudo rm /etc/nginx/sites-available/default
    sudo cp /app/docker/nginx/app.conf /etc/nginx/sites-available/app.conf
    sudo sed -i 's~php_fpm:9000~unix:/var/run/php/php8.3-fpm.sock~g' /etc/nginx/sites-available/app.conf
    sudo sed -i 's~/opt/bitnami/nginx/logs/~/var/log/nginx/~g' /etc/nginx/sites-available/app.conf
    sudo ln -s /etc/nginx/sites-available/app.conf /etc/nginx/sites-enabled/app.conf
    sudo service nginx restart

    # Set MySQL password
    sudo mysql -u root <<-EOF
        CREATE USER 'passwordBroker'@'%' IDENTIFIED BY 'passwordBroker';
        CREATE DATABASE passwordBroker;
        GRANT ALL PRIVILEGES ON passwordBroker.* TO 'passwordBroker'@'%';
        FLUSH PRIVILEGES;
EOF

    php /app/artisan migrate
    sudo echo "* *    * * *   root    php /app/artisan schedule:run >> /dev/null 2>&1" >> /etc/crontab
    php /app/artisan queue:work --tries=3 --timeout=90 --daemon &

  SHELL
end
