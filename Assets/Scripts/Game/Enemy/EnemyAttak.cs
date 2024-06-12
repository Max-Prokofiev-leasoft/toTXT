using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class EnemyAttak : MonoBehaviour
{
    [SerializeField]
    private float _damageAmount;

    private void OnCollisionStay2D(Collision2D collision)
    {
        if (collision.gameObject.GetComponent<PlayerMovement>())
        {
            var healthController = collision.gameObject.GetComponent<HeathController>();
            healthController.TakeDamage(_damageAmount);
        };
    }
}
