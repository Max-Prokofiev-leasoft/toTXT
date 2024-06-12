using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.InputSystem;

public class PlayerShoot : MonoBehaviour
{
    [SerializeField]
    private GameObject _bulletPrefab;

    [SerializeField]
    private float _bulletSpeed;

    [SerializeField]
    private Transform _gunOffset;

    [SerializeField]
    private float _timeBetweenShots;

    private bool _fireContinously;
    private float _lastFireTime;

    // Update is called once per frame
    void Update()
    {
        if (_fireContinously)
        {
            float timeSinceLastFire = Time.time - _lastFireTime;

            if (timeSinceLastFire >= _timeBetweenShots)
            {
                FireBullet();

                _lastFireTime = Time.time;
            }
        }
    }

    private void FireBullet()
    {
        // Get the position of the cursor in the game world
        Vector3 mousePosition = Camera.main.ScreenToWorldPoint(Mouse.current.position.ReadValue());
        mousePosition.z = 0f; // Ensure the z-coordinate is 0 (2D plane)

        // Calculate direction vector from player's position to cursor position
        Vector3 direction = (mousePosition - transform.position).normalized;

        // Instantiate bullet at gunOffset position
        GameObject bullet = Instantiate(_bulletPrefab, _gunOffset.position, Quaternion.identity);

        // Set bullet's velocity to move in the direction of the cursor
        Rigidbody2D bulletRigidbody = bullet.GetComponent<Rigidbody2D>();
        bulletRigidbody.velocity = direction * _bulletSpeed;

        Debug.Log("Bullet Fired!");
    }

    private void OnFire(InputValue inputValue)
    {
        _fireContinously = inputValue.isPressed;
    }
}
