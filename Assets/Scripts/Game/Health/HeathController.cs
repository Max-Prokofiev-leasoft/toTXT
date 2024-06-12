using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Events;

public class HeathController : MonoBehaviour
{
    // Start is called before the first frame update
    [SerializeField]
    private float _currentHealth;

    [SerializeField]
    private float _maxHealth;

    public float RemainHealthPercent
    {
        get
        {
            return _currentHealth / _maxHealth;
        }
    }

    public bool isInvicible { get; set; }

    public UnityEvent OnDied;

    public UnityEvent OnDamaged;

    public UnityEvent OnHealthChanged;

    public void TakeDamage(float DamageAmount)
    {
        if(_currentHealth == 0)
        {
            return;
        }

        if (isInvicible)
        {
            return;
        }

        _currentHealth -= DamageAmount;

        OnHealthChanged.Invoke();

        if(_currentHealth < 0)
        {
            _currentHealth = 0;
        }

        if (_currentHealth == 0)
        {
            OnDied.Invoke();
        }
        else
        {
            OnDamaged.Invoke();
        }

    }

    public void addHealth(float healAmount)
    {
        if(_currentHealth == _maxHealth)
        {
            return;
        }

        _currentHealth += healAmount;

        OnHealthChanged.Invoke();

        if (_currentHealth > _maxHealth)
        {
            _currentHealth = _maxHealth;
        }
    }

}
