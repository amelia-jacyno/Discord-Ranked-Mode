RED="\e[31m"
GREEN="\e[32m"
BLUE="\e[34m"
RESET="\e[0m"

color_echo() {
  local color="$1"
  shift
  echo -e "${color}$@${RESET}"
}