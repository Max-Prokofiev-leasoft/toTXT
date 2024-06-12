using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class playerDamagedInvincibility : MonoBehaviour
{
    [SerializeField]
    private float _invinceDuration;

    private Inivicibility _invince;

    private void Awake()
    {
        _invince = GetComponent<Inivicibility>();
    }

    public void starInvincible()
    {
        _invince.startInvicibile(_invinceDuration);
    }
}
