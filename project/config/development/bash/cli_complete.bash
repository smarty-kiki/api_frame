__frame_comp ()
{
    COMPREPLY=($(compgen -W "$1" "$2"))
    return
}

_cli_complete ()
{
    local cur="${COMP_WORDS[COMP_CWORD]}"

    case "$COMP_CWORD" in
        1)
        __frame_comp "`/usr/bin/php /var/www/api_frame/public/cli.php | awk '{print $1}'`" $cur
            return
            ;;
        2)
            local group_name=${COMP_WORDS[1]}
        __frame_comp "`/usr/bin/php /var/www/api_frame/public/cli.php | awk '{print $1}' | grep $group_name: | cut -d ':' -f 2 | awk '{print \"\\\\\"$1}'`" "\\"
            return
            ;;
        3)
            local group_name=${COMP_WORDS[1]}
        __frame_comp "`/usr/bin/php /var/www/api_frame/public/cli.php | awk '{print $1}' | grep $group_name: | cut -d ':' -f 2`" $cur
            return
            ;;
        esac
            return
}

alias cli="/usr/bin/php /var/www/api_frame/public/cli.php"
complete -o default -o nospace -F _cli_complete cli
