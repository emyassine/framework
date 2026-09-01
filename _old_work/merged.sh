#!/usr/bin/env bash

set -euo pipefail

# Terminal styling
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

get_term_width() {
    tput cols 2>/dev/null || echo 80
}

print_banner() {
    local width
    width=$(get_term_width)
    echo -e "${CYAN}//> Merge Files and Folders.${NC}"
    echo -e "${CYAN}//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine${NC}"
    echo -e "${CYAN}//> Yassine El Moumen <yassine@numerimondes.com>${NC}"
    echo -e "${CYAN}// Licensed under the Eclipse Public License 2.0 (EPL-2.0).${NC}"
    echo -e "${BLUE}$(printf '%.0s=' $(seq 1 "$width"))${NC}\n"
}

output_path=""
raw_inputs=()
raw_excludes=()

# Clean strings (strip trailing/leading whitespace and stray commas)
clean_token() {
    local val="$1"
    val=$(echo "$val" | sed -e 's/^,*//' -e 's/,*$//' | xargs)
    echo "$val"
}

# Parse CLI arguments intelligently
while [[ $# -gt 0 ]]; do
    case "$1" in
        --dir=*|--dirs=*)
            IFS=',' read -r -a split_dirs <<< "${1#*=}"
            for item in "${split_dirs[@]}"; do
                c_item=$(clean_token "$item")
                [[ -n "$c_item" ]] && raw_inputs+=("$c_item")
            done
            shift
            ;;
        --dir|--dirs)
            shift
            while [[ $# -gt 0 ]] && [[ "$1" != --* ]]; do
                IFS=',' read -r -a split_dirs <<< "$1"
                for item in "${split_dirs[@]}"; do
                    c_item=$(clean_token "$item")
                    [[ -n "$c_item" ]] && raw_inputs+=("$c_item")
                done
                shift
            done
            ;;
        --exclude=*|--excludes=*)
            IFS=',' read -r -a split_excl <<< "${1#*=}"
            for item in "${split_excl[@]}"; do
                c_item=$(clean_token "$item")
                [[ -n "$c_item" ]] && raw_excludes+=("$c_item")
            done
            shift
            ;;
        --exclude|--excludes)
            shift
            while [[ $# -gt 0 ]] && [[ "$1" != --* ]]; do
                IFS=',' read -r -a split_excl <<< "$1"
                for item in "${split_excl[@]}"; do
                    c_item=$(clean_token "$item")
                    [[ -n "$c_item" ]] && raw_excludes+=("$c_item")
                done
                shift
            done
            ;;
        --output=*|--out=*)
            output_path="${1#*=}"
            shift
            ;;
        --output|--out|-o)
            output_path="$2"
            shift 2
            ;;
        *)
            # Capture trailing or unflagged positional paths directly
            if [[ "$1" != -* ]]; then
                IFS=',' read -r -a split_dirs <<< "$1"
                for item in "${split_dirs[@]}"; do
                    c_item=$(clean_token "$item")
                    [[ -n "$c_item" ]] && raw_inputs+=("$c_item")
                done
                shift
            else
                echo -e "${RED}Error: Unknown option '$1'${NC}" >&2
                exit 1
            fi
            ;;
    esac
done

print_banner

# Interactive mode if no paths were provided
if [ ${#raw_inputs[@]} -eq 0 ]; then
    echo -e "${BOLD}${YELLOW}Interactive Mode Enabled${NC}"
    echo -e "Enter file or directory paths (use ${BOLD}Tab${NC} for completion). Press ${BOLD}Enter${NC} on empty input when finished.\n"

    bind 'set completion-ignore-case on' 2>/dev/null || true

    path_idx=1
    while true; do
        read -e -p "$(echo -e "${BOLD}${GREEN}Path #${path_idx}:${NC} ")" input_entry
        input_entry=$(clean_token "$input_entry")

        if [ -z "$input_entry" ]; then
            if [ ${#raw_inputs[@]} -gt 0 ]; then
                break
            else
                echo -e "${RED}Error: Provide at least one path.${NC}"
                continue
            fi
        fi

        eval expanded_entry="$input_entry"

        if [ -e "$expanded_entry" ]; then
            raw_inputs+=("$expanded_entry")
            ((path_idx++))
        else
            echo -e "${YELLOW}Warning: Path '$input_entry' does not exist. Try again.${NC}"
        fi
    done

    # Optional interactive excludes
    read -e -p "$(echo -e "${BOLD}${YELLOW}Exclusions (comma-separated files/dirs/extensions, optional):${NC} ")" user_excludes
    if [ -n "$user_excludes" ]; then
        IFS=',' read -r -a split_excl <<< "$user_excludes"
        for item in "${split_excl[@]}"; do
            c_item=$(clean_token "$item")
            [[ -n "$c_item" ]] && raw_excludes+=("$c_item")
        done
    fi

    if [ -z "$output_path" ]; then
        echo ""
        read -e -i "merged.txt" -p "$(echo -e "${BOLD}${GREEN}Output file path:${NC} ")" user_output
        output_path=$(clean_token "$user_output")
    fi
fi

# Set default output
if [ -z "$output_path" ]; then
    output_path="merged.txt"
fi

# Determine incremental output path
target_output="$output_path"
if [ -e "$target_output" ]; then
    parent_dir=$(dirname "$output_path")
    file_base=$(basename "$output_path")
    extension=""
    file_prefix="$file_base"

    if [[ "$file_base" == *.* ]]; then
        extension=".${file_base##*.}"
        file_prefix="${file_base%.*}"
    fi

    counter=1
    while [ -e "${parent_dir}/${file_prefix}.${counter}${extension}" ]; do
        ((counter++))
    done
    target_output="${parent_dir}/${file_prefix}.${counter}${extension}"
fi

# Ensure target directory exists
parent_dir=$(dirname "$target_output")
mkdir -p "$parent_dir"

# Binary file check
is_binary_file() {
    local target_file="$1"
    if [ ! -f "$target_file" ]; then
        return 0
    fi
    local mime_type
    mime_type=$(file -b --mime-encoding "$target_file" 2>/dev/null || echo "binary")
    if [ "$mime_type" = "binary" ]; then
        return 0
    fi
    return 1
}

# Resolve and validate exclusion rules
resolved_excludes=()
for excl in "${raw_excludes[@]}"; do
    eval expanded_excl="$excl"
    resolved_excludes+=("$expanded_excl")
done

is_excluded() {
    local target="$1"
    local real_target
    real_target=$(realpath "$target" 2>/dev/null || echo "$target")

    # Always skip output target file
    local real_output
    real_output=$(realpath "$target_output" 2>/dev/null || echo "$target_output")
    if [ "$real_target" = "$real_output" ]; then
        return 0
    fi

    # Binary check
    if is_binary_file "$target"; then
        return 0
    fi

    # Explicit exclude matching
    for excl in "${resolved_excludes[@]}"; do
        local real_excl
        real_excl=$(realpath "$excl" 2>/dev/null || echo "$excl")

        if [ "$real_target" = "$real_excl" ]; then
            return 0
        fi

        if [ -d "$excl" ] && [[ "$real_target" == "$real_excl"* ]]; then
            return 0
        fi

        local base_name
        base_name=$(basename "$target")
        if [[ "$base_name" == $excl ]] || [[ "$target" == $excl ]]; then
            return 0
        fi
    done

    return 1
}

# Evaluate input paths (Directories, Files, Globs)
valid_paths=()
for raw_path in "${raw_inputs[@]}"; do
    eval expanded_path="$raw_path"
    if [ -e "$expanded_path" ]; then
        valid_paths+=("$expanded_path")
    else
        echo -e "${YELLOW}Warning: Path '$expanded_path' does not exist. Skipping.${NC}" >&2
    fi
done

if [ ${#valid_paths[@]} -eq 0 ]; then
    echo -e "${RED}Error: No valid paths to process.${NC}" >&2
    exit 1
fi

echo -e "${BLUE}Merging contents into: ${BOLD}${target_output}${NC}"

# Execute aggregation
{
    echo "=================================================="
    echo "STRUCTURE"
    echo "=================================================="
    for path in "${valid_paths[@]}"; do
        if [ -d "$path" ]; then
            echo -e "\n--- Directory: $path ---"
            if command -v tree >/dev/null 2>&1; then
                tree "$path"
            else
                find "$path"
            fi
        elif [ -f "$path" ]; then
            echo -e "\n--- File: $path ---"
        fi
    done

    echo -e "\n=================================================="
    echo "FILE CONTENTS"
    echo "=================================================="

    for path in "${valid_paths[@]}"; do
        if [ -d "$path" ]; then
            while IFS= read -r -d '' item; do
                if is_excluded "$item"; then
                    continue
                fi

                echo "--------------------------------------------------"
                echo ">>> FILE: $item"
                echo -e "--------------------------------------------------\n"
                cat "$item"
                echo -e "\n"
            done < <(find "$path" -type f ! -path '*/.*' -print0)
        elif [ -f "$path" ]; then
            if ! is_excluded "$path"; then
                echo "--------------------------------------------------"
                echo ">>> FILE: $path"
                echo -e "--------------------------------------------------\n"
                cat "$path"
                echo -e "\n"
            fi
        fi
    done
} > "$target_output"

term_width=$(get_term_width)
echo -e "${GREEN}Process completed: ${BOLD}${target_output}${NC}"
echo -e "${BLUE}$(printf '%.0s=' $(seq 1 "$term_width"))${NC}"
