#!/bin/bash

base_dir=$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)

# Define the directories and output files
scss_dir="$base_dir/scss"
dist_dir="$base_dir/dist"
last_mod_file="$base_dir/dist/last_css"

mkdir -p $dist_dir

output_file="$base_dir/dist/style.scss"
output_file_css="$base_dir/dist/style.css"

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

# Check if global.scss exists and add it first
if [[ -f "$scss_dir/global.scss" ]]; then
    cat "$scss_dir/global.scss" >> $output_file
fi

# Loop through all SCSS files in the directory, excluding start.scss and global.scss
for file in $scss_dir/*.scss; do
    if [[ "$(basename $file)" != "global.scss" ]]; then
        cat "$file" >> $output_file
    fi
done

echo "Compile the SCSS to CSS"

hash=$(sha256sum "$output_file" | cut -c 1-10)

rm $base_dir/dist/style*.css* -f
file_name="style-$hash.min.css"
final_file="$base_dir/dist/$file_name"
rev_file="$base_dir/dist/rev-css.json"
sass $output_file:$final_file
sass $output_file:$output_file_css
echo "{\"style.css\":\"$file_name\"}" > $rev_file 


# Save the latest modification time
echo $latest_mod > $last_mod_file
