#!/usr/bin/env bash

function __colima_confirm() {
  echo -e "🛠️ \033[34mThis script will perform the following actions on your system:\033[0m"
  echo -e "    1. Install Colima, Docker, Docker Compose, and Docker Buildx using Homebrew."
  echo -e "    2. Configure environment variables for Colima and Docker in your shell configuration files (.bashrc and .zshrc)."
  echo -e "    3. Create symlinks for Docker Compose and Buildx CLI plugins to your Docker configuration."
  echo -e "    4. Set up Colima's default configuration with optimized settings for your architecture."
  echo -e "    5. Symlink /var/run/docker.sock to Colima's Docker socket at \033[33munix://${HOME}/.colima/default/docker.sock\033[0m."

  echo -e "\n⚠️ \033[33mPlease ensure you understand the changes this script will make to your machine.\033[0m"
  echo -e "These changes include modifying your shell configuration files, creating symlinks, and installing packages via Homebrew."

  read -rp "Do you wish to proceed? (yes/no): " response
  if [[ "$response" != "yes" ]]; then
      echo -e "❌ \033[31mOperation canceled by user.\033[0m"
      exit 1
  fi

  echo -e "✅ \033[32mUser confirmed. Proceeding with setup...\033[0m"
  echo -e "\n"
}

function __colima_validate_env() {
  echo -e "🔍 \033[34mValidating environment...\033[0m"

  # Check if Docker Desktop is installed
  if [ -d "/Applications/Docker.app" ]; then
    echo -e "❌ \033[31mDocker Desktop is installed. Please uninstall Docker Desktop before proceeding.\033[0m"
    echo -e "   - Open your Applications folder."
    echo -e "   - Right-click on Docker.app and select 'Move to Trash'."
    exit 1
  else
    echo -e "✅ \033[32mDocker Desktop is not installed.\033[0m"
  fi

  # Check if /var/run/docker.sock exists and is a symlink
  if [[ -L /var/run/docker.sock ]]; then
      echo -e "✅ \033[32m/var/run/docker.sock is a symlink.\033[0m"
  elif [[ -e /var/run/docker.sock ]]; then
      echo -e "❌ \033[31m/var/run/docker.sock exists but is not a symlink. Please remove it.\033[0m"
      exit 1
  else
      echo -e "⚠️ \033[33m/var/run/docker.sock does not exist. This will be created during setup.\033[0m"
  fi

  # Check if /usr/local/bin/docker is symlinked to podman
  if [[ -L /usr/local/bin/docker ]] && [[ "$(readlink /usr/local/bin/docker)" == *"podman"* ]]; then
      echo -e "❌ \033[31m/usr/local/bin/docker is symlinked to Podman. Please remove this symlink.\033[0m"
      exit 1
  else
      echo -e "✅ \033[32m/usr/local/bin/docker is not symlinked to Podman.\033[0m"
  fi

  # Check if /usr/local/bin/docker-compose is symlinked to podman-compose
  if [[ -L /usr/local/bin/docker-compose ]] && [[ "$(readlink /usr/local/bin/docker-compose)" == *"podman-compose"* ]]; then
      echo -e "❌ \033[31m/usr/local/bin/docker-compose is symlinked to Podman Compose. Please remove this symlink.\033[0m"
      exit 1
  else
      echo -e "✅ \033[32m/usr/local/bin/docker-compose is not symlinked to Podman Compose.\033[0m"
  fi

  # Source the user's shell configuration file to load aliases
  source "$HOME/.zshrc" >/dev/null 2>&1
  source "$HOME/.bashrc" >/dev/null 2>&1

  # Check if docker is aliased to podman
  if alias docker >/dev/null 2>&1; then
    if [ `alias docker | wc -l` != 0 ]; then
        echo -e "❌ \033[31mYou have aliased 'docker' to Podman. Please remove this alias before continuing.\033[0m"
        echo -e "   - Remove the alias by editing your shell configuration file (e.g., ~/.bashrc or ~/.zshrc)."
        exit 1
    else
        echo -e "✅ \033[32m'docker' is not aliased to Podman.\033[0m"
    fi
  fi

  # Check if docker-compose is aliased to podman-compose
  if alias docker-compose >/dev/null 2>&1; then
    if [ `alias docker-compose | wc -l` != 0 ]; then
        echo -e "❌ \033[31mYou have aliased 'docker-compose' to Podman Compose. Please remove this alias before continuing.\033[0m"
        echo -e "   - Remove the alias by editing your shell configuration file (e.g., ~/.bashrc or ~/.zshrc)."
        exit 1
    else
        echo -e "✅ \033[32m'docker-compose' is not aliased to Podman Compose.\033[0m"
    fi
  fi

  echo -e "🌟 \033[32mEnvironment validation complete.\033[0m"
  sleep 1
  echo -e "\n"
}

function __colima_setup_env() {
  echo -e "🔧 \033[34mInstalling Colima, Docker, and Docker Compose...\033[0m"
  if ! command -v "brew" >/dev/null 2>&1; then
      echo -e "⚠️ \033[33mHomebrew is not installed. Please install Homebrew first.\033[0m"
      exit 1
  fi

  brew install --quiet lima colima docker docker-compose docker-Buildx

  echo -e "📄 \033[34mAdding environment variables to .bashrc and .zshrc...\033[0m"

  env_vars='
  \nexport LIMA_HOME="$HOME/.colima/_lima/"
  \nexport COLIMA_HOME="$HOME/.colima"
  \nexport DOCKER_HOST="unix://${COLIMA_HOME}/default/docker.sock"
  '

  for rc_file in "$HOME/.bashrc" "$HOME/.zshrc"; do
      if [[ -f $rc_file ]]; then
          if ! grep -q "export LIMA_HOME" "$rc_file"; then
              echo -e "$env_vars" >> "$rc_file"
          fi
      fi
  done

  sleep 1
  echo -e "\n"
}

function __colima_setup_docker() {
  mkdir -p ~/.docker/cli-plugins

  ln -sfn "$(brew --prefix)/opt/docker-compose/bin/docker-compose" ~/.docker/cli-plugins/docker-compose
  ln -sfn "$(brew --prefix)/opt/docker-buildx/bin/docker-buildx" ~/.docker/cli-plugins/docker-buildx
}

function __colima_post_install() {
  echo -e "\n🎉 \033[32mSetup complete!\033[0m"
  echo -e "Here are some directions and recommendations for users with Podman installed:\n"

  echo -e "1. \033[34mStop your Podman machine (if running) & cleanup any podman-provisioned containers:\033[0m"
  echo -e "   - Run \033[33mpodman rm -afi\033[0m to remove any containers built with Podman.\n"
  echo -e "   - Run \033[33mpodman rmi -afi\033[0m to remove any images in Podman.\n"
  echo -e "   - Run \033[33mpodman volume prune -f\033[0m to remove any volumes in Podman.\n"
  echo -e "   - Run \033[33mpodman machine stop\033[0m to stop the Podman machine and free up resources.\n"
  echo -e "   - Run \033[33mpodman machine rm\033[0m to remove the machine altogether.\n"

  echo -e "2. \033[34mUninstall Podman:\033[0m"
  echo -e "   - Run \033[33msudo podman-mac-helper uninstall\033[0m to remove Podman <> Docker compatibility."
  echo -e "   - Run \033[33mbrew uninstall podman-compose podman\033[0m to remove Podman if installed with brew."
  echo -e "   - Run \033[33mrm -rf /opt/podman\033[0m to remove Podman if installed without brew."
  echo -e "   - If you have other dependencies relying on Podman, review them before uninstalling.\n"

  # Podman Desktop instructions
  echo -e "3. \033[34mUsing Podman Desktop GUI:\033[0m"
  echo -e "   - If you prefer using a GUI to manage containers, enable Docker experimental compatibility mode in Podman Desktop's settings."
  echo -e "   - Navigate to Settings > Preferences > Docker Compatibility and flip the switch.\n"

  # Colima instructions
  echo -e "4. \033[34mStart Colima:\033[0m"
  echo -e "   - Run \033[33mcolima delete\033[0m to cleanup any currently running Colima configuration."
  echo -e "   - Run \033[33mcolima start\033[0m to start Colima."
  echo -e "   - Alternatively, you can enable Colima to start automatically on system boot:"
  echo -e "     - Run \033[33mbrew services start colima\033[0m to achieve this.\n"

  # Colima instructions
  echo -e "4. \033[34mCommon issues and how to resolve them:\033[0m"
  echo -e "   \033[33merror getting credentials - err: exec: \"docker-credential-desktop\": executable file not found in \$PATH, out: \`\`\033[0m"
  echo -e "     - Remove the line \033[33m\"credsStore\": \"...\"\033[0m from your \033[33m~/.docker/config.json\033[0m.\n"

  # Confirmation prompt
  echo -e "⚠️ \033[33mPlease ensure you understand these recommendations before proceeding with further changes.\033[0m"
  read -p "Do you acknowledge and understand these recommendations? (yes/no): " response
  if [[ "$response" != "yes" ]]; then
      echo -e "❌ \033[31mOperation canceled. Please review the recommendations carefully.\033[0m"
      exit 1
  fi

  echo -e "✅ \033[32mUser acknowledged the recommendations. You're good to go!\033[0m"
}

function __colima_setup_testcontainers() {
  echo "The following flow, will setup testcontainers configurations, (only relevant if you're maintaining legacy Java projects)."
  read -rp "Do you wish to proceed? (yes/no): " response

  response=$(echo "$response" | tr '[:upper:]' '[:lower:]')
  if [[ "$response" == "yes" || "response" == "y" ]]; then
    echo -e "🛠 \033[34mThis script will modify .testcontainers.properties file in your home directory.\033[0m"
    if [[ -f ~/.testcontainers.properties ]]; then
      echo -e "📄 \033[34mCurrent .testcontainers.properties file content:\033[0m"
      cat ~/.testcontainers.properties
    fi
    cat > ~/.testcontainers.properties <<EOF
#Modified by Yotpo Colima setup script https://github.com/YotpoLtd/ops-helpers/blob/master/lib/scripts/setup-colima.sh
#$(date)

docker.host=unix://${HOME}/.colima/default/docker.sock
docker.socket.override=/var/run/docker.sock
testcontainers.reuse.enable=true
EOF
    echo -e "✅ \033[32m.testcontainers.properties file created successfully!\033[0m"
    echo -e "📄 \033[34mAdding environment variable to .bashrc and .zshrc...\033[0m"
    for rc_file in "$HOME/.bashrc" "$HOME/.zshrc"; do
        if [[ -f $rc_file ]]; then
            if ! grep -q "export TESTCONTAINERS_RYUK_DISABLED=true" "$rc_file"; then
                echo -e "export TESTCONTAINERS_RYUK_DISABLED=true" >> "$rc_file"
            fi
        fi
    done
    echo -e "✅ \033[32mTestcontainers setup complete!\033[0m"
  else
    echo -e "⏭️ \033[31mTestcontainers setup skipped by user.\033[0m"
  fi
}

function setup_colima() {
  __colima_confirm
  __colima_validate_env
  __colima_setup_env
  __colima_setup_docker

  arch=$(uname -m)

  if [[ "$arch" == "arm64" ]]; then
    colima_arch="aarch64"
    colima_vmtype="vz"
    colima_rosetta=true
    colima_mount_type="virtiofs"
  elif [[ "$arch" == "x86_64" ]]; then
    colima_arch="host"
    colima_vmtype="qemu"
    colima_rosetta=false
    colima_mount_type="sshfs"
  else
      echo -e "❓ \033[31mUnknown architecture: $arch\033[0m"
      exit 1
  fi

  echo -e "⚙️ \033[33mSymlinking Colima socket to /var/run/docker.sock...\033[0m"
  sudo ln -sf "${HOME}/.colima/default/docker.sock" /var/run/docker.sock

  echo -e "⚙️ \033[34mSetting up Colima default.yaml configuration...\033[0m"

  template_dir="$HOME/.colima/_templates"
  template_file="$template_dir/default.yaml"

  mkdir -p "$template_dir"

  cat > "$template_file" <<EOF
cpu: 4
disk: 100
memory: 8
arch: ${colima_arch}
runtime: docker
hostname: ""
kubernetes:
  enabled: false
  version: v1.31.2+k3s1
  k3sArgs:
    - --disable=traefik
autoActivate: true
network:
  address: true
  dns: []
  dnsHosts:
    host.docker.internal: host.lima.internal
  hostAddresses: false
forwardAgent: false
docker: {}
vmType: ${colima_vmtype}
rosetta: ${colima_rosetta}
nestedVirtualization: false
mountType: ${colima_mount_type}
mountInotify: false
cpuType: host
provision:
  - mode: system
    script: apt-get install btop vim
sshConfig: true
sshPort: 0
mounts: []
diskImage: ""
env: {}
EOF

  __colima_setup_testcontainers
  __colima_post_install

  echo -e "✅ \033[32mInstallation and configuration complete!\033[0m"
  echo -e "🔄 \033[32mRestart your shell or source your shell configuration file to apply changes.\033[0m"
}