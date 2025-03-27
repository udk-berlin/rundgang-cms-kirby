import pandas as pd
import json
import argparse
import os
from typing import Dict, Any, List, Union
from collections import defaultdict
import logging

# Configure logging
logging.basicConfig(
    level=logging.INFO, format="%(asctime)s - %(levelname)s - %(message)s"
)


def process_sheet(
    df: pd.DataFrame,
    sheet_name: str,
    university_name: str = "Universität der Künste Berlin",
) -> Dict[str, Any]:
    """
    Process a single dataframe (sheet) and convert to nested structure

    Parameters:
    -----------
    df : pandas.DataFrame
        The dataframe to process
    sheet_name : str
        Name of the sheet for logging and default faculty name
    university_name : str
        Name of the institution/university

    Returns:
    --------
    dict
        The faculty data structure with the faculty name as the key
    """
    # Create a case-insensitive mapping of actual column names
    column_map = {col.lower(): col for col in df.columns}
    logging.info(f"Sheet {sheet_name} - Column map: {column_map}")

    # Try to find the right columns
    faculty_col = None
    institute_col = None
    course_col = None
    class_col = None

    for col_lower, actual_col in column_map.items():
        if "fakultät" in col_lower or "einrichtung" in col_lower:
            faculty_col = actual_col
        elif "institut" in col_lower:
            institute_col = actual_col
        elif "studiengang" in col_lower:
            course_col = actual_col
        elif (
            "fachgebiet" in col_lower
            or "fachklasse" in col_lower
            or "klasse" in col_lower
        ):
            class_col = actual_col

    logging.info(
        f"Sheet {sheet_name} - Detected columns: faculty={faculty_col}, institute={institute_col}, "
        f"course={course_col}, class={class_col}"
    )

    # Process faculty data
    faculty_name = sheet_name
    if faculty_col and not df[faculty_col].dropna().empty:
        # If faculty column exists and has values, try to get a single faculty name
        unique_faculties = df[faculty_col].dropna().unique()
        if len(unique_faculties) == 1:
            faculty_name = unique_faculties[0]  # Use the single faculty name
        else:
            # If multiple faculties, we'll handle that later
            pass

    # Create faculty object with the faculty name as the key
    faculty_data = {"type": "faculty", "parent": university_name, "children": {}}

    # Handle cases where faculty name comes from column:
    if faculty_col and len(df[faculty_col].dropna().unique()) > 1:
        # Multiple faculties in this sheet - process each one separately
        faculties = df[faculty_col].dropna().unique()

        # We'll return a dict of faculty data
        faculty_dict = {}

        # Create a placeholder for items with missing faculty
        missing_faculty_name = f"{sheet_name} (unspecified faculty)"
        missing_faculty = {"type": "faculty", "parent": university_name, "children": {}}

        # First, process rows with faculty values
        for faculty_name in faculties:
            current_faculty = {
                "type": "faculty",
                "parent": university_name,
                "children": {},
            }

            # Filter for this faculty
            faculty_df = df[df[faculty_col] == faculty_name]

            # Process institutes for this faculty
            if institute_col:
                institutes = process_institutes(
                    faculty_df, institute_col, course_col, class_col, faculty_name
                )
                if institutes:
                    current_faculty["children"] = institutes

            faculty_dict[faculty_name] = current_faculty

        # Now process rows with missing faculty value
        missing_faculty_df = df[df[faculty_col].isna()]

        if not missing_faculty_df.empty:
            if institute_col:
                institutes = process_institutes(
                    missing_faculty_df,
                    institute_col,
                    course_col,
                    class_col,
                    missing_faculty_name,
                )
                if institutes:
                    missing_faculty["children"] = institutes

            # Only add if it has children
            if missing_faculty["children"]:
                faculty_dict[missing_faculty_name] = missing_faculty

        return faculty_dict
    else:
        # Single faculty - process its institutes
        if institute_col:
            institutes = process_institutes(
                df, institute_col, course_col, class_col, faculty_name
            )
            if institutes:
                faculty_data["children"] = institutes

        return {faculty_name: faculty_data}


def process_institutes(df, institute_col, course_col, class_col, parent_name):
    """
    Helper function to process institutes for a faculty
    Handles rows with missing institute values
    Returns a dictionary with institute names as keys
    """
    institutes_dict = {}

    # First process rows with institute values
    if institute_col in df.columns:
        institutes = df[df[institute_col].notna()][institute_col].unique()

        for institute_name in institutes:
            institute_data = {
                "type": "institute",
                "parent": parent_name,
                "children": {},
            }

            # Filter for this institute
            institute_df = df[df[institute_col] == institute_name]

            # Process courses
            if course_col:
                courses = process_courses(
                    institute_df, course_col, class_col, institute_name
                )
                if courses:
                    institute_data["children"] = courses

            institutes_dict[institute_name] = institute_data

        # Now process rows with missing institute value
        missing_institute_df = df[df[institute_col].isna()]

        if not missing_institute_df.empty and course_col:
            # Create a placeholder institute for items with missing institute
            missing_institute_name = "Unspecified Institute"
            missing_institute = {
                "type": "institute",
                "parent": parent_name,
                "children": {},
            }

            courses = process_courses(
                missing_institute_df, course_col, class_col, missing_institute_name
            )
            if courses:
                missing_institute["children"] = courses

            # Only add if it has courses
            if missing_institute["children"]:
                institutes_dict[missing_institute_name] = missing_institute
    else:
        # No institute column - create a default institute
        default_institute_name = "Default Institute"
        default_institute = {"type": "institute", "parent": parent_name, "children": {}}

        # Process courses directly
        if course_col:
            courses = process_courses(df, course_col, class_col, default_institute_name)
            if courses:
                default_institute["children"] = courses

            # Only add if it has courses
            if default_institute["children"]:
                institutes_dict[default_institute_name] = default_institute

    return institutes_dict


def process_courses(df, course_col, class_col, parent_name):
    """
    Helper function to process courses for an institute
    Handles rows with missing course values
    Returns a dictionary with course names as keys
    """
    courses_dict = {}

    # First process rows with course values
    if course_col in df.columns:
        courses = df[df[course_col].notna()][course_col].unique()

        for course_name in courses:
            course_data = {"type": "course", "parent": parent_name, "children": {}}

            # Filter for this course
            course_df = df[df[course_col] == course_name]

            # Process classes
            if class_col:
                classes = process_classes(course_df, class_col, course_name)
                if classes:
                    course_data["children"] = classes

            courses_dict[course_name] = course_data

        # Now process rows with missing course value
        missing_course_df = df[df[course_col].isna()]

        if not missing_course_df.empty and class_col:
            # Create a placeholder course for items with missing course
            missing_course_name = "Unspecified Course"
            missing_course = {"type": "course", "parent": parent_name, "children": {}}

            classes = process_classes(missing_course_df, class_col, missing_course_name)
            if classes:
                missing_course["children"] = classes

            # Only add if it has classes
            if missing_course["children"]:
                courses_dict[missing_course_name] = missing_course
    else:
        # No course column - create a default course
        default_course_name = "Default Course"
        default_course = {"type": "course", "parent": parent_name, "children": {}}

        # Process classes directly
        if class_col:
            classes = process_classes(df, class_col, default_course_name)
            if classes:
                default_course["children"] = classes

            # Only add if it has classes
            if default_course["children"]:
                courses_dict[default_course_name] = default_course

    return courses_dict


def process_classes(df, class_col, parent_name):
    """
    Helper function to process classes for a course
    Returns a dictionary with class names as keys
    """
    classes_dict = {}

    if class_col in df.columns:
        classes = df[class_col].dropna().unique()

        for class_name in classes:
            if (
                pd.notna(class_name) and class_name.strip()
            ):  # Check if value is not empty or whitespace
                class_data = {
                    "type": "class",
                    "parent": parent_name,
                    "children": {},  # Empty children dict for leaf nodes
                }
                classes_dict[class_name] = class_data
    else:
        # If there's no class column but we have rows, create a default class
        if not df.empty:
            default_class_name = "Default Class"
            classes_dict[default_class_name] = {
                "type": "class",
                "parent": parent_name,
                "children": {},  # Empty children dict for leaf nodes
            }

    return classes_dict


def excel_to_nested_json(
    excel_file: str,
    output_file: str = None,
    sheets: List[str] = None,
    indent: int = 2,
    university_name: str = "Universität der Künste Berlin",
) -> Dict[str, Any]:
    """
    Convert Excel file to nested JSON following the hierarchy:
    institution -> faculties -> institutes -> courses -> classes
    Using a parent-child structure where node names are keys in the JSON structure.

    Parameters:
    -----------
    excel_file : str
        Path to the Excel file
    output_file : str, optional
        Path to the output JSON file. If None, won't save to file but return the data
    sheets : list of str, optional
        Specific sheet names to convert. If None, processes all sheets
    indent : int, optional
        Number of spaces for indentation in the output JSON file
    university_name : str, optional
        Name of the institution/university

    Returns:
    --------
    dict
        The converted data as a nested dictionary
    """
    # Check if file exists
    if not os.path.exists(excel_file):
        raise FileNotFoundError(f"Excel file not found: {excel_file}")

    # Get Excel file info
    xl = pd.ExcelFile(excel_file)
    available_sheets = xl.sheet_names

    # Determine which sheets to process
    if sheets is None:
        sheets_to_process = available_sheets
        logging.info(f"No sheets specified. Processing all sheets: {sheets_to_process}")
    else:
        # Make sure all requested sheets exist (case-insensitive)
        available_sheets_lower = [s.lower() for s in available_sheets]
        sheets_to_process = []

        for sheet in sheets:
            sheet_lower = sheet.lower()
            if sheet_lower in available_sheets_lower:
                sheet_index = available_sheets_lower.index(sheet_lower)
                sheets_to_process.append(available_sheets[sheet_index])
            else:
                raise ValueError(
                    f"Sheet '{sheet}' not found in Excel file. Available sheets: {available_sheets}"
                )

    # Create the main structure with university name as the key
    result = {
        university_name: {
            "type": "institution",
            "parent": None,  # Top-level has no parent
            "children": {},
        }
    }

    # Process each sheet
    for sheet_name in sheets_to_process:
        logging.info(f"Processing sheet: {sheet_name}")
        df = pd.read_excel(excel_file, sheet_name=sheet_name)

        # Skip empty sheets
        if df.empty:
            logging.warning(f"Sheet {sheet_name} is empty, skipping")
            continue

        # Process the sheet
        faculty_dict = process_sheet(df, sheet_name, university_name)

        # Add to main structure
        if faculty_dict:
            result[university_name]["children"].update(faculty_dict)

    # Save to file if output_file is specified
    if output_file:
        with open(output_file, "w", encoding="utf-8") as f:
            json.dump(result, f, indent=indent, ensure_ascii=False)
        logging.info(f"Nested JSON data saved to {output_file}")

    return result


def main():
    parser = argparse.ArgumentParser(
        description="Convert Excel file to nested JSON with keys as node names"
    )
    parser.add_argument("excel_file", help="Path to the Excel file")
    parser.add_argument("-o", "--output", help="Path to the output JSON file")
    parser.add_argument(
        "-s",
        "--sheets",
        nargs="+",
        help="Specific sheet names to convert (default: all sheets)",
    )
    parser.add_argument(
        "--indent", type=int, default=2, help="Number of spaces for indentation"
    )
    parser.add_argument(
        "--university",
        default="Universität der Künste Berlin",
        help="University/institution name",
    )
    parser.add_argument("--debug", action="store_true", help="Enable debug logging")

    args = parser.parse_args()

    # Set logging level
    if args.debug:
        logging.getLogger().setLevel(logging.DEBUG)

    # If no output file is specified, use the Excel filename with .json extension
    if not args.output:
        args.output = os.path.splitext(args.excel_file)[0] + ".json"

    try:
        excel_to_nested_json(
            args.excel_file, args.output, args.sheets, args.indent, args.university
        )
        logging.info("Conversion completed successfully!")
    except Exception as e:
        logging.error(f"Error: {e}")
        import traceback

        logging.error(traceback.format_exc())
        return 1

    return 0


if __name__ == "__main__":
    exit(main())

