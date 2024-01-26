#!/bin/bash

# Define the directories and output files
scss_dir="./scss"
dist_dir="./dist"
last_mod_file="./dist/last"

mkdir -p $dist_dir

output_file="./dist/style.scss"
final_file="./dist/style.css"

# Get the latest modification time in the scss_dir
latest_mod=$(find $scss_dir -type f -name '*.scss' -exec stat -c '%Y' {} \; | sort -n | tail -1)

# Check if the last_mod_file exists and compare the modification times
if [[ -f $last_mod_file ]]; then
    last_mod=$(cat $last_mod_file)
    if [[ $latest_mod -le $last_mod ]]; then
        echo "No new changes in SCSS files."
        exit 0
    fi
fi

echo "" > $output_file

# Check if start.scss exists and add it first
if [[ -f "$scss_dir/start.scss" ]]; then
    cat "$scss_dir/start.scss" >> $output_file
fi

# Check if global.scss exists and add it first
if [[ -f "$scss_dir/global.scss" ]]; then
    cat "$scss_dir/global.scss" >> $output_file
fi

# Loop through all SCSS files in the directory, excluding start.scss and global.scss
for file in $scss_dir/*.scss; do
    if [[ "$(basename $file)" != "global.scss" ]] && [[ "$(basename $file)" != "start.scss" ]]; then
        cat "$file" >> $output_file
    fi
done

echo "Compile the SCSS to CSS"
sass $output_file:$final_file

# Save the latest modification time
echo $latest_mod > $last_mod_file
