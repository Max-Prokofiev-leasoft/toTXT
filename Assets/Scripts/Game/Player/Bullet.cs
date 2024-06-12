using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Bullet : MonoBehaviour
{
    private Camera _camera;

    private void Awake()
    {
        _camera = Camera.main;
    }

    private void Update()
    {
        RotateTowardsCursor();
        DestroyOffScreen();
    }

    private void OnTriggerEnter2D(Collider2D collision)
    {
        Debug.Log("Bullet collided with: " + collision.gameObject.name);

        if (collision.GetComponent<Enemy>())
        {
            Debug.Log("Enemy hit!");

            // Destroy the enemy and the bullet
            Destroy(collision.gameObject);
            Destroy(gameObject);
        }
    }

    private void RotateTowardsCursor()
    {
        // Get the direction vector from the bullet's current position to the cursor position
        Vector3 mousePosition = _camera.ScreenToWorldPoint(Input.mousePosition);
        Vector3 direction = mousePosition - transform.position;
        direction.z = 0f; // Ensure direction is on the 2D plane

        // Calculate the rotation angle in degrees
        float angle = Mathf.Atan2(direction.y, direction.x) * Mathf.Rad2Deg;

        // Apply the rotation to the bullet sprite
        transform.rotation = Quaternion.AngleAxis(angle, Vector3.forward);
    }

    private void DestroyOffScreen()
    {
        Vector2 screenPosition = _camera.WorldToScreenPoint(transform.position);

        if (screenPosition.x < 0 || screenPosition.x > _camera.pixelWidth || screenPosition.y < 0 || screenPosition.y > _camera.pixelHeight)
        {
            Destroy(gameObject);
        }
    }
}
