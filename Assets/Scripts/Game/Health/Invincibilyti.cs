using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Inivicibility : MonoBehaviour
{
    private HeathController _healthController;

    private void Awake()
    {
        _healthController = GetComponent<HeathController>();
    }

    public void startInvicibile(float invincibilityDuration)
    {
        StartCoroutine(InvincCour(invincibilityDuration));
    }

    private IEnumerator InvincCour(float invincibilityDuration)
    {
        _healthController.isInvicible = true;
        yield return new WaitForSeconds(invincibilityDuration);
        _healthController.isInvicible = false;
    }
}
