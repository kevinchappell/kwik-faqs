# Importing FAQs from JSON

The Kwik FAQs plugin now supports importing FAQs from a JSON file. This feature allows you to quickly add multiple FAQs to your site without manually creating each one.

## JSON File Format

The JSON file should be an array of objects, where each object represents a FAQ with a "question" and "answer" key:

```json
[
  {
    "question": "What is Kwik FAQs?",
    "answer": "Kwik FAQs is a simple WordPress plugin that allows you to easily create and manage Frequently Asked Questions on your website."
  },
  {
    "question": "How do I install Kwik FAQs?",
    "answer": "You can install Kwik FAQs by going to Plugins > Add New in your WordPress admin, searching for \"Kwik FAQs\", and clicking Install Now."
  }
]
```

## How to Import FAQs

1. Prepare your JSON file with the FAQ data in the format shown above
2. Go to your WordPress admin panel
3. Navigate to FAQs > Import
4. Click "Choose File" and select your JSON file
5. Click "Import FAQs"
6. You should see a success message indicating how many FAQs were imported

## Requirements

- The JSON file must be properly formatted
- Each FAQ object must have both "question" and "answer" keys
- The file must have a .json extension