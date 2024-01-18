#!/bin/bash

# Define the directory and output file
css_dir="./css"



# Check for --watch argument
if [ "$1" == "--watch" ]; then
    echo "Watching for changes"

    # Command to execute when a change is detected
    on_change_command="./gen-style.sh"

    # Debounce delay in seconds
    debounce_delay=2

    # Last run time initialization
    last_run=0

    # Watching for changes in the directory
    inotifywait -m -e modify,create,delete "$css_dir" --format '%w%f %e' |
        while read file event; do
            echo "Detected $event on $file"

            # Check if the file name contains 'style.css'
            if [[ "$file" != *"style"* ]]; then
                current_time=$(date +%s)
                if (( current_time - last_run > debounce_delay )); then
                    eval "$on_change_command"
                    last_run=$current_time
                fi
            fi
        done
else

    output_file="./style.css"


    echo "" > $output_file

    # Check if global.css exists and add it first
    if [[ -f "$css_dir/start.css" ]]; then
        cat "$css_dir/start.css" >> $output_file
    fi

    # Check if global.css exists and add it first
    if [[ -f "$css_dir/global.css" ]]; then
        echo "@import \"$css_dir/global.css\";" >> $output_file
    fi

    # Loop through all CSS files in the directory, excluding global.css
    for file in $css_dir/*.css; do
        if [[ "$(basename $file)" != "global.css" ]] && [[ "$(basename $file)" != "start.css" ]]; then
            echo "@import \"$file\";" >> $output_file
        fi
    done

    # echo "Style file created: $output_file"
    npm run css
fi
