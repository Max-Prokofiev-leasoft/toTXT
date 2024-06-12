using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class HealthBar : MonoBehaviour
{
    [SerializeField]
    private UnityEngine.UI.Image _healthFG;

    public void barUpdate(HeathController HealthConrtroller)
    {
        _healthFG.fillAmount = HealthConrtroller.RemainHealthPercent;
    }
}